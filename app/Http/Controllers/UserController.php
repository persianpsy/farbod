<?php

namespace App\Http\Controllers;

use App\Http\Requests\getInfoUserStatusRequest;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\StoreUser;
use App\Http\Requests\UpdateClientUserRequest;
use App\Http\Requests\UserAuthRequest;
use App\Http\Traits\ResponseTrait;
use App\Models\Category;
use App\Models\CategoryStaff;
use App\Models\Media;
use App\Models\Staff;
use App\Models\Wallet;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\MediaRepository;
use App\Repositories\Repository;
use App\Repositories\UserRepository;
use App\Transformers\StaffInfoTransformer;
use App\Transformers\StaffENInfoTransformer;
use Firebase\JWT\JWT;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Stevebauman\Location\Facades\Location;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;


class UserController extends BaseController
{
    use ResponseTrait;

    private $model;
    private $staffModel;
    private $userRepository;
    private $mediaRepository;
    const PATH = 'avatars';

    public function __construct(UserRepository $userRepository,MediaRepository $mediaRepository, User $user,Staff $staff,Media $media)
    {
        $this->model = new Repository($user);
        $this->staffModel = new Repository($staff);
        $this->mediaRepository = $mediaRepository;
        $this->userRepository = $userRepository;
    }

    public function preLogin(Request $request)
    {
        return $this->userRepository->preLogin($request);
    }

    public function login(Request $request)
    {
        return $this->userRepository->login($request);
    }
    public function edit(Request $request, User $user)
    {
        return $this->userRepository->update($request, $user);
    }

    public function preRegister(Request $request)
    {
        return $this->userRepository->preRegister($request);
    }
    public function showUser(Request $request)
    {
        return $this->handleResponse(\App\Models\User::with('wallet')
            ->where('id',$request->user()->id)->first(),'shown user!');
    }
     public function showDoctor(Request $request)
    {
        $data = \App\Models\User::with('wallet')
            ->where('id',$request->user()->id)->first();

        if($data){
            $staff = \App\Models\Staff::where('user_id',$data->id)->first();
            if($staff){
                 return $this->handleResponse($data,'shown user!');
            }else {
                 return $this->handleError([],' error shown staff!');
            }
        } else {
            return $this->handleError([],' error shown user!');
        }

    }
    public function preAuth(StoreUser $request)
    {

        return $this->userRepository->preAuth($request);
    }
     public function preEmailAuth(Request $request)
    {

        return $this->userRepository->preEmailAuth($request);
    }
    public function preDoctorAuth(StoreUser $request)
    {
        $cellphone = $request->cellphone;
        $staff = Staff::query()->with('user')->whereHas('user',function($q) use($cellphone){
            $q->where('cellphone', '=', $cellphone);
        })->first();
        if(!$staff) {
            return $this->handleError([],'no permission for auth');
        }
        return $this->userRepository->preAuth($request);
    }

    public function update(UpdateClientUserRequest $request)
    {
        return $this->userRepository->update($request);
    }

    public function auth(UserAuthRequest $request)
    {
        return $this->userRepository->auth($request,'personal-client');
    }

    public function authForAdmin(Request $request)
    {
        if($request->cellphone === '00989124484707' || $request->cellphone === '00989128175866' || $request->cellphone === '00989120647142' || $request->cellphone === '00989122721593' || $request->cellphone === '00989335192412')
        {
            return $this->userRepository->auth($request,'all-admin');

        }else{
            return false;
        }

    }

    public function authForDoctor (UserAuthRequest $request)
    {
         $cellphone = $request->cellphone;
        $staff = Staff::query()->with('user')->whereHas('user',function($q) use($cellphone){
        $q->where('cellphone', '=', $cellphone);
        })->first();
        if(!$staff) {
            return $this->handleError([],'no permission for auth');
        }
        return $this->userRepository->authForDoctor($request,$staff,'doctor-staff');

    }


    public function getOtp(Request $request)
    {
        return $this->userRepository->getOtp($request);
    }

    public function register(Request $request)
    {
        return $this->userRepository->register($request);
    }

    public function logout(Request $request)
    {
        return $this->userRepository->logout($request);
    }
    public function destroy(Request $request,User $user)
    {
        return $this->userRepository->delete($user);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        return $this->userRepository->update($request,$user);
    }

    public function index(Request $request, User $user = null)
    {
        // return User::with('wallet')->get();
        $conditions = array();
        if ($user)
            $conditions['id'] = $user->id;

        if ($request->conditions)
            $conditions = json_decode($request->conditions, true);

        $model = $this->model;
        if ($conditions)
            $model = $this->model->list($conditions);

        if ($request->with)
            $model = $model->with($request->with);

        if($request->role){
            $role = $request->role;
            $model = $model->whereHas('roles',function($q) use ($role){
                $q->where("name", $role);
            });
        }

        if ($conditions || $request->with || $request->role) {
            if ($request->noPaginate) {
                return $model->get();
            }
            return $model->paginate();
        }
        if ($request->noPaginate) {
            return $this->model->all();
        } else {
            return $this->model->paginate();
        }
    }

    public function adminIndex(Request $request)
    {

        $model = $this->model;

        $model = $model->with('wallet');
        $model->orderBy('created_at', 'DESC');
        return ['data'=>$model->paginate(),'total'=>$model->count()];

    }
    public function adminLog(Request $request)
    {


        return DB::table('log_activity')->orderBy('created_at', 'DESC')->get();


    }
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @param User|null $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexDoctors(Request $request,Staff $user = null)
    {

        if (cache()->has('staff.transform.final')) {

          $users =cache()->get('staff.transform.final');

          return $users ;
          return $this->handleResponse(fractal($users,new StaffInfoTransformer() )->transform(),'found Staff!');

        }


         $data = Staff::with('user','category_staff','category_staff.category');
        // if($request->staff_id)
        // {

        //     $data->where('id',$request->staff_id);
        //     cache()->put('staff.index',$data->get());
        // }
         $user = cache()->put('staff.transform.final',$this->handleResponse(fractal($data->get(),new StaffInfoTransformer() )->transform(),'final Staff!'));

         return $user ;



        // $data = Staff::with('user','category_staff','category_staff.category');
        // if($request->staff_id)
        // {
        //     $data->where('id',$request->staff_id);
        // }
        // return $this->handleResponse(fractal($data->get(),new StaffInfoTransformer() )->transform(),'found Staff!');

    }

      public function indexENDoctors(Request $request,Staff $user = null)
    {

        if (cache()->has('staff.transform.en.final')) {

          $users =cache()->get('staff.transform.en.final');

          return $users ;


        }


         $data = Staff::with('user','category_staff','category_staff.category');

         $user = cache()->put('staff.transform.en.final',$this->handleResponse(fractal($data->get(),new StaffENInfoTransformer() )->transform(),'final Staff!'));

         return $user ;



        // $data = Staff::with('user','category_staff','category_staff.category');
        // if($request->staff_id)
        // {
        //     $data->where('id',$request->staff_id);
        // }
        // return $this->handleResponse(fractal($data->get(),new StaffInfoTransformer() )->transform(),'found Staff!');

    }
     public function indexDoctorsSepicific(Request $request,Staff $user = null)
    {
        $data = Staff::with('user');
        $name = explode("-", $request->name);
        $user_id = User::query()->where('en_first_name',$name[0])->where('en_last_name',$name[1])->first()->id;
            $data->where('user_id',$user_id);

        return $this->handleResponse(fractal($data->get(),new StaffInfoTransformer() )->transform(),'found Staff!');

    }


    public function changeWallet(Request $request)
    {

        $payment = new Payment();

        $payment->user_id = $request->user()->id;
        $payment->gateway = 'دستی';
        $payment->status = 2;
        $payment->price = $request->amount;

        $payment->save();

        $user = Wallet::query()->where('user_id',$request->user)->first();

        $user->amount = $request->amount;

        $user->save();


        return $this->handleResponse($user,'change wallet !');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeDoctors(StoreDoctorRequest $request)
    {

        $data = $request->only($this->model->getModel()->fillable);

        $user_info = User::query()->where('cellphone',$request->cellphone)->first();

        if ($user_info)
        {
            return $this->handleError('exists user!',[]);
        }
        $user_info = $this->model->create($data);

        /*$user->syncRoles('doctor');*/

        $staffData = $request->only($this->staffModel->getModel()->fillable);
        $staffData['user_id']=$user_info->id;

        $staff_info = $this->staffModel->create($staffData);


        if ($request->image) {
            $destination_path ='/image/doctors/profile';
            $image_name = Verta::now()->format('Hmjs').rand(0,1000000);
            $path = $request->image->storeAs($destination_path,$image_name,"public");
            $staff_info->image     = env('APP_URL').'/public/storage/'.$path;

        }

        if ($request->cover) {
            $destination_path ='/image/doctors/cover';
            $image_name = Verta::now()->format('Hmjs').rand(0,1000000);
            $path = $request->cover->storeAs($destination_path,$image_name,"public");
            $staff_info->cover     = env('APP_URL').'/public/storage/'.$path;
        }

//        if ($request->category){
//            $categories = json_decode($request->category,true);
//            foreach ($categories as $category){
//                CategoryStaff::create([
//                    'category_id'=>$category['category_id'],
//                    'user_id'=>$user_info->id
//                ]);
//            }
//        }


        $staff_info->save();

        return $this->handleResponse($data,'created staff!');
    }

    /**
     * Display the specified resource.i
     *
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function showDoctors(User $uesr)
    {
        return $this->model->show($uesr);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function updateDoctors(Request $request, User $user)
    {
        $userData = $request->only($this->model->getModel()->fillable);

        if(isset($request->password)){
            $userData['password'] = Hash::make($request->password);
        }
        $updateUser = $this->model->update($userData, $user);
        $staff = $user->staff;
        if ($staff) {
            $staffData = $request->only($this->staffModel->getModel()->fillable);
            $newStaff = $this->staffModel->update($staffData,$staff);
            if ($request->category){
                $categories = json_decode($request->category,true);
                CategoryStaff::where('user_id',$user->id)->delete();
                foreach ($categories as $category){
                    CategoryStaff::create([
                        'category_id'=>$category['category_id'],
                        'user_id'=>$user->id
                    ]);
                }
            }
            return $newStaff;
        }
        return response()->json(['msg'=>'عملیات به روز رسانی موفق نبود!'],419);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Question  $question
     * @return \Illuminate\Http\Response
     */
    public function destroyDoctors(User $uesr)
    {
        return $this->model->delete($uesr);
    }

    public function getBalance(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user)
            return response()->json(['کاربر مشخص نشده است'],400);

        return $this->response('با موفقیت دریافت شد.',['dollar_balance'=>$user->dollar_balance,'toman_balance'=>$user->toman_balance,'dollar_balance_removable'=>$user->dollar_balance_removable,'toman_balance_removable'=>$user->toman_balance_removable]);
    }

    public function getIp(Request $request)
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        $real_ip = $ip;
                    }
                }
            }
        }
        //It will return server/LB ip when no client ip found
        $real_ip=request()->ip();
        $status = Http::get('http://ip-api.com/json/'.$real_ip);
        return $this->response('success get data',['loc'=>$status]);
    }

    public function zoomSignature(Request $request)
    {
//        //Set the timezone to UTC
        date_default_timezone_set("UTC");
        $time = time() * 1000 - 30000;//time in milliseconds (or close enough)
//        $data = base64_encode($request->api_key . $request->meetingNumber . $time . $request->role);
//        $hash = hash_hmac('sha256', $data, $request->api_secret, true);
//        $_sig = $request->api_key . "." . $request->meetingNumber . "." . $time . "." . $request->role . "." . base64_encode($hash);
//        //return signature, url safe base64 encoded
//        return rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
        $key = 'XS2SzEO5bBruAGkB5qNS64rnFQzf3vAdk6M3';
        $sdkSecret = 'eAaG4FWPA9SSjQ39rZjW1GdCscpwwtH1RA4s';
        $payload = array(
            'sdkKey'=>$key,
            'mn'=> $request->meetingNumber,
            'role'=> 1,
            'iat:'=>$time,
            'exp'=> $time + 500000,
            'appKey'=> $key,
            'tokenExp'=> $time + 500000
        );
        return JWT::encode($payload, $sdkSecret,'HS256');
    }
    public function getStatus(getInfoUserStatusRequest $request)
    {
        $user = User::query()->where('id',$request->user()->id)->first();
        if ($user)
        {
            $err = 1 ;

            if($user->email)
            {
               $err=$err+1 ;
            }





            return $this->handleResponse($err/2,'user found!');
        } else {

            return  $this->handleError('not found user !',[]);
        }
    }
    public  function loginUser(Request $request): bool
    {
        return Auth::guard('api')->check();
    }

     public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

     public function add(Request $request)
    {
      return $this->userRepository->joinUserAdmin($request);
    }
}
