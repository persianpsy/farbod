<?php namespace App\Repositories;

use App\Classes\LocationStatus;
use App\Classes\PhoneStatus;
use App\Http\ApiResponse;
use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreUser;
use App\Http\Requests\UserAuthRequest;
use App\Http\Traits\DateTrait;
use App\Http\Traits\ResponseTrait;
use App\Http\Traits\SmsTrait;
use App\Http\Traits\NotificationTrait;
//use App\Models\Coach;
//use App\Models\Manager;
//use App\Models\Player;
//use App\Models\Seller;
use App\Models\User;
use App\Jobs\smsReminder;
use App\Models\Wallet;
use App\Notifications\NewUser;
use App\Notifications\WelcomeNewUser;
use App\Transformers\AuthTransformer;
use Carbon\Carbon;
use Hashids\Hashids;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\Concerns\Has;
use Validator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Client;
use Hekmatinasser\Verta\Verta;
use Jenssegers\Agent\Agent;

use Illuminate\Contracts\Queue\ShouldQueue;

class UserRepository extends BaseController implements ShouldQueue
{
    use DateTrait,ResponseTrait,SmsTrait,NotificationTrait;
    private $mediaRepository;
    private $model;

    const AVATAR_PATH = 'media/users/avatars';
    public function __construct(MediaRepository $mediaRepository,User $user)
    {
        $this->mediaRepository = $mediaRepository;
        $this->model = $user;

    }

    private function issueToken(Request $request,$scope = 'personal-client')
    {

        $user = User::query()->where('cellphone' ,$request->cellphone)->first();
        if(!$user){
             $user = User::query()->where('email' ,$request->email)->first();
        }



        return $user->createToken('UserToken', [$scope]);
    }
    public function sendOTP($user,$request){

        $code = $this->generateCode();

        $hashids = new Hashids();

        $user->auth_code = $hashids->encode($code);

        if ($request->location != 'IR' || substr($request->cellphone,0,4) != '0098')
        {
            $user->location = LocationStatus::OUT ; // NOT IR;

            $info = array([

                'messages' => array([
                    "content" => "OTP from persianpsychology  : ".$code,
                    "recipients" => [ $request->cellphone],

                    "channel" => 'sms',
                    "msg_type" => "text",
                    "data_coding"=> "text"

                ])  ,
                "message_globals" => array([
                    "originator" => "SignOTP",
                    "report_url" => "https://the_url_to_recieve_delivery_report.com"])
            ]);



            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.d7networks.com/messages/v1/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>json_encode($info[0]),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhdWQiOiJhdXRoLWJhY2tlbmQ6YXBwIiwic3ViIjoiY2Y2MTQ4NjQtN2UwMC00NDlhLTgzMzEtYWZiNDZkNTA3NjZiIn0.xepRsG1yUm-K1JSs4yV8eHe-2APsyfW614q_M1q93LI'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);




        } else{
            $res = $this->SendAuthCode($request->cellphone,'welcome',$code);
            $user->location = LocationStatus::IR ; //IR;
        }
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $user->ip = $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }


        $user->save();

        $description = serialize([
            'event'  => 'pre Login Api',
            'input'  => $request->ip(),
            'header' => $request->header('user-agent'),
            'user'   => $user->cellphone,

        ]);
        activity()->log($description);

        return $this->handleResponse([],'send otp');
    }
    public function preAuth(StoreUser $request)
    {
        $user = $this->firstOrNewUser($request);

        if ($request->model = 'email' )
        {
            $this->emailAuth($request);
        }

        $this->sendOTP($user,$request);
    }
    public function preEmailAuth (Request $request)
    {

        $user = User::query()->where('email',$request->email)->first();

        if (!$user){
            return $this->handleError([],'auth.wrong');
        }


    }
     public function authForDoctor($request, $staff,$scope)
    {

        // user registration is Not Complete
        // check hash codes

           $hashids = new Hashids();

        if ($request->otp != $hashids->decode($staff->user->auth_code)[0])
        {
            return $this->handleError([],'wrong auth');
        }


        // if ($staff->phone_verified_at = '0') {
        //     $user->phone_verified_at = '1';
        //     $user->save();
        // }

        $data = $this->issueToken($request,$scope);


         $staff->user->token = $data->accessToken ;
         $staff->user->save();

        // if (!$request->location = 'IR') {
        //     $location = '1';
        // } else {
        //     $location = '0';
        // }

        // $wallet = Wallet::query()->where('user_id',$staff->user->id)->first();

        // if(!$wallet)
        // {
        //     $wallet = Wallet::query()->where('user_id',$user->id)->firstOrCreate([
        //         'user_id' => $user->id,
        //         'location' => $location
        //     ]);
        // }

        // $res = [
        //     'data' => $data,
        //     'wallet' => $wallet
        // ];

        return $this->handleResponse($data,'ok!');
        //not ok ??

    }
    public function auth( $request,$scope = 'personal-client')
    {
        $user = $this->findUserByCellphone($request->cellphone);

        if(!$user)

            return $this->handleError([],'not found auth');

        $hashids = new Hashids();

        if ($request->otp != $hashids->decode($user->auth_code)[0])
        {
            return $this->handleError([],'رمز یکبار مصرف اشتباه است');
        }

        $user->phone_verified_at = PhoneStatus::VERIFIED;

         $token = $this->issueToken($request,$scope);
         $user->token = $token->accessToken ;
         $user->save();

        if($user->email){
            $total  = true;
        } else {
            $total = false ;
        }

        $wallet = Wallet::query()->where('user_id',$user->id)->first();
        if (!$wallet)
            if($user->location == 1){
               Wallet::query()->insert([
                'user_id'  =>  $user->id,
                'currency' =>  $user->location,
                'amount'   =>  1000000
            ]);

             $res = $this->SendAuthCode($user->cellphone,'salam','کاربر');
            } else {
              Wallet::query()->insert([
                'user_id'  =>  $user->id,
                'currency' =>  $user->location,
                'amount'   =>  5
            ]);
            }

            $wallet = Wallet::query()->where('user_id',$user->id)->first();




        return $this->handleResponse([
            'token' => $token->accessToken,'currency' =>  $wallet->currency , 'total' => $total
        ],'به پنل خوش آمدید ');
        //not ok ??

    }
       public function emailAuth( $request,$scope = 'personal-client')
    {
        $user = User::query()->where('email',$request->email)->orwhere('cellphone',$request->cellphone)->first();

        if(!$user)
            return $this->handleError([],'not found auth');

        $token = $this->issueToken($request,$scope);

        $wallet = Wallet::query()->where('user_id',$user->id)->first();
        if (!$wallet)
            Wallet::query()->insert([
                'user_id'  =>  $user->id,
                'currency' =>  $user->location
            ]);
            $wallet = Wallet::query()->where('user_id',$user->id)->first();

        return $this->handleResponse([
            'token' => $token->accessToken,'currency' =>  $wallet->currency
        ],'welcome auth!');
        //not ok ??

    }
    private function assignUserRole($type,User $user,Request $request){
        try {
            return $user->syncRoles($type);
        }catch (\Exception $exception){
            return $this->response(__('validation.custom.wrongInfo'),$exception,400);
        }
    }

    private function checkHashCode(User $user,$code,$field){
        if ($field=='password')
            return Hash::check($code,$user->password);
        else
            return  Hash::check($code,$user->auth_code);

    }

    public function getToken($cellphone,$client,$request)
    {
        $user = $this->findUserByCellphone($cellphone);
        $data = $this->issueToken($request, 'password')->getContent();
        $data_array = json_decode($data,true);
        $expire_date = Carbon::now()->addSeconds($data_array['expires_in'])->timestamp;
        $data_array['expires_in']=$expire_date;
        return [$data_array,$user];
//        return $token->plainTextToken;
    }
    public function findUserByCellphone($cellphone)
    {
        $data =  User::where('cellphone', $cellphone)->first();
        if($data){
            return $data;
        }
        return false;


    }
    public function findUserByEmail($email)
    {
        try{
            return User::where('email', $email)->firstOrFail();
            //do some stuff
        } catch (ModelNotFoundException $e){
            //treat error (log the activity, redirect to a certain page)
            //or display a 404 page
            //dealer's choice
            return null;
        }
    }
    public function findUserByNationalCode($national_code)
    {
        try{
            return User::where('national_code', $national_code)->firstOrFail();
            //do some stuff
        } catch (ModelNotFoundException $e){
            //treat error (log the activity, redirect to a certain page)
            //or display a 404 page
            //dealer's choice
            return null;
        }
    }
    public function firstOrCreateUser($data)
    {
        return User::firstOrCreate(['cellphone' => $data['cellphone']],$data);
    }
    public function firstOrNewUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cellphone' => 'required',
        ],[
//            'cellphone.unique'    => 'کاربری با این تلفن قبلا ثبت نام شده است.از طریق صفحه ورود، وارد سایت شوید.',
            'cellphone.required'      => 'وارد کردن شماره تلفن همراه ضروری است.',
        ]);

        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        $res = $this->findUserByCellphone($request->cellphone);
       if($res){
           return $res;
       }
        $res = $this->findUserByEmail($request->email);
        if($res){
            return $res;
        }
        return User::firstOrNew([
            'cellphone' => $request->cellphone,
            'email'     => $request->email,
            'password'  => $request->password
        ],$request->only([
            'cellphone'
        ]));
    }
    private function changeAvatar(User $user,Request $request){
        if ($user->avatar){
            $this->mediaRepository->delete($user->avatar,self::AVATAR_PATH);
            return $this->mediaRepository->upload($request->avatar,self::AVATAR_PATH);
        }else{
            return $this->mediaRepository->upload($request->avatar,self::AVATAR_PATH);
        }
    }
    private function updateUser(Request $request){

        $user = User::query()->where('id',$request->user()->id)->first();
        if($request->first_name)
         $user->first_name = $request->first_name ;
        if($request->en_first_name)
            $user->en_first_name = $request->en_first_name ;
        if($request->last_name)
            $user->last_name = $request->last_name ;
        if($request->en_last_name)
            $user->en_last_name = $request->en_last_name ;
        if($request->email){
            $user->email = $request->email ;
        }
        if($request->jobs){
        $user->jobs = $request->jobs ;
        }
        if($request->birthday){
        $user->birthday = $request->birthday ;
        }
        if($request->education){
            $user->education = $request->education ;
        }
        if($request->gender){
            $user->gender = $request->gender ;
        }
        if ($request->has('image')) {
            $destination_path ='/user/profile';
            $image_name = Verta::now()->format('Hmjs').rand(0,1000000);
            $path = $request->image->storeAs($destination_path,$image_name,"public");
            $user->avatar     = 'https://api.persianpsychology.com'.'/public/storage/'.$path;
        }
           return $user->save();
    }
    public function preLogin(Request $request)
    {
        $user = $this->findUserByCellphone($request->cellphone);
        if (!$user)
            return $this->response(__('auth.user_not_found'),[],309);

        $code = $this->generateCode();
        $user->update([
            'auth_code'=>Hash::make($code)
        ]);
//        return $this->sendOTP($user,$code);
    }

    public function login(Request $request,$refreshToken = null)
    {
        if (isset($refreshToken)){
            $client = Client::where('name','application')->first();
            $params = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'scope' => ''
            ];
            $request->request->add($params);

            $proxy = Request::create('oauth/token', 'POST');
            $res = Route::dispatch($proxy);

            $data = json_decode($res->getContent(), true);
            if (isset($data['error']))
                return $this->response('',[$data],400);
            if (isset($data['expires_in']))
                $data['expires_in'] = Carbon::now()->addSeconds($data['expires_in'])->timestamp;
            return $this->response('', [$data]);
        }
        else {
            $user = $this->findUserByCellphone($request->cellphone);

            if (!$user)
                return $this->response(__('auth.user_not_found'), [], 422);

//            if (is_null($user->password)){
//                $code = $this->generateCode();
//                $user->update([
//                    'auth_code'=>Hash::make($code)
//                ]);
//                $this->sendOTP($user,$code);
//                return $this->response(__('auth.need_refresh_password'), [], 423);
//            }
            $hash_check = $this->checkHashCode($user, (isset($request->code))?$request->code:$request->password, $request->type);

            if (!$hash_check)
                return $this->response(__('auth.password'), [], 422);

            $data = json_decode($this->issueToken($request), true);
            if (isset($data['error']))
                return $this->response('',[$data],400);
            $data['expires_in'] = Carbon::now()->addSeconds($data['expires_in'])->timestamp;
            $permissions_array = $user->getAllPermissions()->pluck('name')->toArray();
            if (is_array($permissions_array))
                $permissions = implode($permissions_array);
            else
                $permissions = $permissions_array;

            return $this->response('', [$data, $user ,
                'roles'=>$user->getRoleNames(),
                'permissions'=>$permissions,'club'=>$user->club,
                'role'=>$user->getRoleNames()[0]]);
        }
    }
    /*
     * Register Logic
     */
    private function updateRegisteredUser(Request $request,User $user){
        if (isset($request->avatar))
            $updated_avatar = $this->changeAvatar($user, $request);
        return $user->update([
            'first_name' => ($request->first_name) ? $request->first_name : $user->first_name,
            'last_name' => ($request->last_name) ? $request->last_name : $user->last_name,
            'password' => ($request->password) ? Hash::make($request->password) : $user->password,
//            'email' => ($request->email) ? $request->email : $user->email,
            'national_code' => ($request->national_code) ? $request->national_code : $user->national_code,
            'residence' => ($request->residence) ? $request->residence : $user->residence,
            'avatar' => ($request->avatar) ? $updated_avatar : $user->avatar,
            'phone' => ($request->phone)?$request->phone:$user->phone,
            'email' => ($request->email)?$request->email:$user->email,
            'address' => ($request->address)?$request->address:$user->address,
            'birth_county' => ($request->birth_county)?$request->birth_county:$user->birth_county,
            'birthday' => ($request->birthday)?$request->birthday:$user->birthday,
            'father_name' => ($request->father_name)?$request->father_name:$user->father_name,
            'desc' => ($request->desc)?$request->desc:$user->desc,
            'resume' => ($request->resume)?$request->resume:$user->resume,
            'auth_code' => ($request->auth_code)?$request->auth_code:$user->auth_code,
            'agent_id' => ($request->agent_id)?$request->agent_id:$user->agent_id,
//            'avatar' => ($request->avatar) ? $updated_avatar : $user->avatar,
//            'address' => ($request->address) ? $request->address : $user->address,
//            'graduation_date'=>($request->graduation_date)?$request->graduation_date:$user->graduation_date,
//            'father_name'=>($request->father_name)?$request->father_name:$user->father_name,
//            'identity_code'=>($request->identity_code)?$request->identity_code:$user->identity_code,
//            'study_field'=>($request->study_field)?$request->study_field:$user->study_field,
//            'university'=>($request->university)?$request->university:$user->university,
//            'introduced_code'=>($request->introduced_code)?$request->introduced_code:$user->introduced_code,
        ]);
    }

    public function preRegister(Request $request)
    {
        $user = $this->firstOrNewUser($request);
        if (is_string($user)){
            return $this->response($user,[],425);
        }
        if ($user->exists)
            return $this->response(__('auth.exists'),[],424);

        $code = $this->generateCode();
        $res = $this->SendAuthCode($request->cellphone,'OTP',$code);

        $user->auth_code = Hash::make($code);
        $user->save();

        $this->assignUserRole('user',$user,$request);

//        $this->RegisterNotifyAdmins($user);

        return $user;
    }

    public function register(Request $request)
    {
        $user = $this->findUserByCellphone($request->cellphone);
        if(!$user)
            return $this->response(__('auth.user_not_found'),[],309);

            // user registration is Not Complete
            // check hash codes
            $checkHashCode = $this->checkHashCode($user,$request->code,'auth_code');

            if (!$checkHashCode)
                return $this->response(__('auth.wrong'),[],409);

            $updated_user = $this->updateRegisteredUser($request,$user);
            //create Player,Coach,Manager in DB
            return $user;
    }

    /*
     * logout Logic
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        $accessToken = $user->token();

        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update(['revoked' => true]);

        $accessToken->revoke();
        return $this->response(__('kicket.responses.success'),[]);
    }

    /*
    * update Logic
    */
    public function update(Request $request)
    {
          $updated_user = $this->updateUser($request);
//        if (!$updated_user)
//            return $this->handleError([],'not updated user !');

        return $this->handleResponse($updated_user,'updated user!');


    }

    public function listFilterByRole($role)
    {
        return User::role($role);
    }

    public function get($fields = null)
    {
        return User::get($fields);
    }

    // remove record from the database
    public function delete($model)
    {
        return $model->delete();
    }

    public function create(Request $request)
    {
        $data = $request->except('avatar','type','store_name');
        if ($request->avatar) {
            $updated_avatar = $this->mediaRepository->upload($request->avatar, self::AVATAR_PATH);
            $data['avatar'] = $updated_avatar;
        }
        $user = $this->firstOrCreateUser($data);
        if (!$user)
            $this->response(__('auth.user_not_updated'),[],204);

        if(isset($request->store_name) && $request->type === 'vendor')
            Seller::create([
                'store_name'=>$request->store_name,
                'store_url'=>Str::slug($request->store_name),
                'user_id'=>$user->id
            ]);

        return $this->assignUserRole($request->type,$user,$request);
    }

    public function edit(Request $request,User $user)
    {
        $updated_user = $this->updateUser($request, $user);
        if (!$updated_user)
            return $this->response(__('auth.user_not_updated'),[],204);
//        if($request->type) {
//            $role = $this->assignUserRole($request->type, $user, $request);
//            return $updated_user && $role;
//        }
        return $user->fresh();
    }

    public function info(Request $request)
    {
        $user = Auth::user();
        if(!$user)
            return response()->json(['error'=>'اطلاعات وارد شده صحیح نیست'],422);
        $permissions_array = $user->getAllPermissions()->pluck('name')->toArray();
        if (is_array($permissions_array))
            $permissions = implode($permissions_array);
        else
            $permissions = $permissions_array;

        $role = 'user';
        if (isset($user->player)){
            $role = $user->player;
        }
        elseif (isset($user->coach)){
            $role = $user->coach;
        }
        elseif (isset($user->manager)){
            $role = $user->manager;
        }
        return ['user'=>$user,'roles'=>$user->roles,'permissions'=>$permissions,'club'=>$user->club,'role'=>$role];

    }

    public function passwordChange(Request $request,User $user)
    {
        return $user->update([
            'password' => ($request->newPassword) ? Hash::make($request->newPassword) : $user->password,
        ]);
    }
    public function preForgot($cellphone)
    {
        $user = $this->findUserByCellphone($cellphone);
        if (is_string($user)){
            return $this->response($user,[],425);
        }
        if (!$user->exists)
            return $this->response(__('auth.not_exists'),[],424);

        $code = $this->generateCode();

        $user->auth_code = Hash::make($code);
        $user->save();

//        return $this->sendOTP($user,$code);
    }
    public function forgot(Request $request)
    {
        $user = $this->findUserByCellphone($request->cellphone);
        if(!$user)
            return $this->response(__('auth.user_not_found'),[],309);

        $checkHashCode = $this->checkHashCode($user,$request->code,'auth_code');
        if (!$checkHashCode)
            return $this->response(__('auth.password'), [], 422);

        if (isset($request->newPassword) && isset($request->newPasswordConfirmation))
            if ($request->newPassword !== $request->newPasswordConfirmation)
                return response()->json('رمز های وارد شده یکسان نیست!',401);

        return $user->update([
            'password' => ($request->newPassword) ? Hash::make($request->newPassword) : $user->password,
        ]);
    }
    public function setPasswordForNull(Request $request)
    {
        $user = $this->findUserByNationalCode($request->national_code);
        if(!$user)
            return $this->response(__('auth.user_not_found'),[],309);

        $checkHashCode = $this->checkHashCode($user,$request->code,'auth_code');
        if (!$checkHashCode)
            return $this->response(__('auth.password'), [], 422);

        if (isset($request->newPassword) && isset($request->newPasswordConfirmation))
            if ($request->newPassword !== $request->newPasswordConfirmation)
                return response()->json('رمز های وارد شده یکسان نیست!',401);

        return $user->update([
            'password' => ($request->newPassword) ? Hash::make($request->newPassword) : $user->password,
        ]);
    }

      public function joinUserAdmin(Request $request)
    {
      $user = new User();
      $user->cellphone = $request->cellphone;

      $user->phone_verified_at = PhoneStatus::VERIFIED;

      $token = $this->issueToken($request,'personal-client');
      $user->token = $token->accessToken ;

      $user->save();

      return $this->handleResponse($user,'shown user!');
    }
}
