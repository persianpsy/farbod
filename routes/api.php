<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\FreeMeetingController;
use App\Http\Controllers\RequestTherapyController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Models\Staff;
use Illuminate\Support\Facades\Mail;
use App\Models\Reservation;
use Illuminate\Support\Facades\Redis;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;

use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Str;
use JoisarJignesh\Bigbluebutton\Facades\Bigbluebutton;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//welcome
Route::get('/welcome', function(Request $request) {
    $uuid = Str::uuid()->toString();
    $agent = new \Jenssegers\Agent\Agent;
    $description = serialize([
        'event' => 'Enter site',
        'input' => $request->ip(),
        'header' => $request->header('user-agent'),
        'robot'  => $agent->isRobot(),
//        'url'    =>  $request->fullUrl(),
        'device' => $agent->device(),
        'browser' => $agent->browser(),
        'uuid'    => $uuid
    ]);
    activity()->log($description);
    return $uuid;
});

//bbb
Route::get('/bbb', function() {
return bigbluebutton()->all();

});

Route::get('free/meeting/index', [FreeMeetingController::class, 'adminInfo'])->name('admin.free.show');

//admin token
Route::group(['namespace' => 'api','prefix'=>'v1','middleware'=>['auth:api','scope:all-admin']], function () {
    Route::get('user/show', [UserController::class, 'show'])->name('user.show');

  Route::post('user/add', [UserController::class, 'add'])->name('user.add');
  Route::get('admin/user/index/', [UserController::class, 'adminIndex'])->name('users.index.admin');
  Route::get('admin/log/index', [UserController::class, 'adminLog'])->name('log.index.admin');
  Route::post('admin/change/wallet', [UserController::class, 'changeWallet'])->name('users.change.wallet');
    Route::get('users/export', [UserController::class, 'export']);
    Route::get('payment/export', [PaymentController::class, 'export']);
    Route::post('doctors/create', [StaffController::class, 'store'])->name('doctors.store');
     Route::post('doctors/image', [StaffController::class, 'storeImage'])->name('doctors.store.image');
    Route::get('staff/index', [StaffController::class, 'show'])->name('doctors.staff.show');
    Route::post('staff/update', [StaffController::class, 'update'])->name('doctors.staff.update');
    Route::get('doctors/show/{user}', [UserController::class, 'showDoctors'])->name('doctors.show');
    Route::post('doctors/{user}/update', [UserController::class, 'updateDoctors'])->name('doctors.update');
    Route::delete('doctors/{user}/delete', [UserController::class, 'destroyDoctors'])->name('doctors.destroy');

    Route::post('admin/appointments/create', [AppointmentController::class, 'storeAdmin'])->name('appointments.store.admin');

    Route::post('admin/appointments/index', [AppointmentController::class, 'indexTotal'])->name('appointments.index.total');

    Route::get('coupon/index', [CouponController::class, 'index'])->name('coupons.index');

    Route::get('request/index', [RequestTherapyController::class, 'index'])->name('request.shown');

    Route::post('blog/create/', [BlogController::class, 'store'])->name('blog.store');
    Route::get('blog/show/{role}', [BlogController::class, 'show'])->name('blog.show');
    Route::post('blog/{role}/update', [BlogController::class, 'update'])->name('blog.update');
    Route::delete('blog/{role}/delete', [BlogController::class, 'destroy'])->name('blog.destroy');

    Route::get('admin/payments/index', [PaymentController::class, 'indexAdmin'])->name('admin.payments.index');

    Route::get('request/status/{id}', [RequestTherapyController::class, 'update'])->name('request.status');


       Route::get('admin/reservations/info', [ReservationController::class, 'adminInfo'])->name('reservations.info.admin');
});

//doctor token
Route::group(['namespace' => 'api','prefix'=>'v1/doctor','middleware'=>['auth:api','scope:doctor-staff']], function () {

    Route::post('appointments/create', [AppointmentController::class, 'store'])->name('appointments.store');
     Route::post('appointments/remove', [AppointmentController::class, 'removeItem'])->name('appointments.remove');

    Route::post('join/meeting', [ReservationController::class, 'joinMeetingRoom'])->name('join.meeting');
    Route::post('clean/meeting', [ReservationController::class, 'cleanRoom'])->name('clean.meeting');
     Route::post('request/index', [RequestTherapyController::class, 'list'])->name('request.list.index');
      Route::post('reservations/closest', [ReservationController::class, 'closest'])->name('reservations.closest');

       Route::post('appointments/index', [AppointmentController::class, 'doctorAppointment'])->name('appointments.shown.doctor');

});

//too dangerous no token
Route::any('payments/verify/{payment}', [PaymentController::class, 'verify'])->name('payment.verify');



//user token and any token
Route::group(['namespace' => 'api','prefix'=>'v1','middleware'=>'auth:api'], function () {
    Route::post('free/store', [FreeMeetingController::class, 'store'])->name('appointments.store.free.doctor');
    Route::get('user/status', [UserController::class, 'getStatus'])->name('users.status');
    Route::post('user/update', [UserController::class, 'update'])->name('update');
    Route::get('/user', [UserController::class, 'showUser'])->name('show.client');
     Route::get('/doctor/show', [UserController::class, 'showDoctor'])->name('show.client');
    Route::get('/user/login', [UserController::class, 'loginUser'])->name('login.client');
     Route::post('coupon/create', [CouponController::class, 'create'])->name('coupons.store');
     Route::post('coupon/discount', [CouponController::class, 'discount'])->name('coupons.discount');
    // Route::post('appointments/index', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::post('total/appointments/index', [AppointmentController::class, 'indexTotal'])->name('appointments.index.total2');

     Route::get('send/receipt/{token}', [PaymentController::class, 'sendReceipt'])->name('send.receipt');

    Route::get('reservations/index/{reservation?}', [ReservationController::class, 'index'])->name('reservations.index');

      Route::get('reservations/info', [ReservationController::class, 'indexUser'])->name('reservations.info');
    Route::post('reservations/create', [ReservationController::class, 'store'])->name('reservations.store');


//    Route::post('reservations/submit', [ReservationController::class, 'submit'])->name('reservations.submit');
    Route::get('reservations/info', [ReservationController::class, 'info'])->name('reservations.info');

     Route::get('reservations/room', [ReservationController::class, 'getRoom'])->name('reservations.room');

    Route::post('wallet/discount', [WalletController::class, 'discount'])->name('wallet.discount');
    Route::post('wallet/charge', [WalletController::class, 'charge'])->name('wallet.charge');

     Route::post('charge/dollar', [PayPalController::class, 'chargeWallet'])->name('chargeWallet');
    Route::get('wallet/info', [WalletController::class, 'info'])->name('wallet.info');

    Route::get('payments/get', [PaymentController::class, 'get'])->name('user.payments.show');

    Route::post('process-transaction', [PayPalController::class, 'processTransaction'])->name('processTransaction');

    Route::post('request/create', [RequestTherapyController::class, 'store'])->name('store.request.therapy');

});

Route::group(['namespace' => 'api','prefix'=>'v1'], function () {

//    Route::post('user/login/{refreshToken?}', [UserController::class, 'login'])->name('login');
////    Route::post('user/pre/register', [UserController::class, 'preRegister'])->name('preRegister');
////    Route::post('user/get/otp', [UserController::class, 'getOtp'])->name('getOtp');

    Route::post('appointments/index', [AppointmentController::class, 'index'])->name('appointments.shown2.doctor');
    Route::get('free/index', [FreeMeetingController::class, 'index'])->name('appointments.get.free.doctor');

    Route::post('test/store', [FreeMeetingController::class, 'storeTest'])->name('test.store.free');
    Route::post('user/pre/auth', [UserController::class, 'preAuth'])->name('preAuth');
    Route::post('user/direct/auth', [UserController::class, 'joinUserDirect'])->name('joinUserDirect');
    Route::post('doctor/pre/auth', [UserController::class, 'preDoctorAuth'])->name('preAuth');
    Route::post('user/auth', [UserController::class, 'auth'])->name('auth');
    Route::post('user/email/auth', [UserController::class, 'emailAuth'])->name('emailAuth');
    Route::post('user/email/pre/auth', [UserController::class, 'preEmailAuth'])->name('preEmailAuth');
    Route::post('doctor/auth', [UserController::class, 'authForDoctor'])->name('authForDoctor');
    Route::post('admin/auth', [UserController::class, 'authForAdmin'])->name('authForAdmin');

     Route::get('doctors/index/{user?}', [UserController::class, 'indexDoctors'])->name('doctors.index');
       Route::get('doctors/en/index/{user?}', [UserController::class, 'indexENDoctors'])->name('doctors.index.en');

      Route::post('doctors/index', [UserController::class, 'indexDoctorsSepicific'])->name('doctors.index.specefic');


//    Route::post('user/register', [UserController::class, 'register'])->name('register');

    Route::get('user/list/{role:name}', [UserController::class, 'listByRole'])->name('list.by.role');
    Route::get('users/index/{user?}', [UserController::class, 'index'])->name('users.index');
    Route::delete('users/{user}/delete', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('users/{user}/update', [UserController::class, 'edit'])->name('users.edit');
    Route::post('users/create', [UserController::class, 'store'])->name('users.store');
    Route::post('user/pre/forgot', [UserController::class, 'preForgot'])->name('preForgot');
    Route::post('users/forgot', [UserController::class, 'forgot'])->name('users.forgot');
    Route::post('users/new/password', [UserController::class, 'setPasswordForNull'])->name('users.forgot');
    Route::get('users/balance', [UserController::class, 'getBalance'])->name('users.balance');
    Route::get('user/ip', [UserController::class, 'getIp'])->name('users.ip');

    Route::get('blog/index/', [BlogController::class, 'index'])->name('blog.index');
//
//    Route::post('media/upload', [MediaController::class, 'upload'])->middleware('auth:api')->name('media.upload');
//    Route::delete('media/{media}/delete', [MediaController::class, 'remove'])->middleware('auth:api')->name('media.delete');
//
//    Route::get('roles/index/{role?}', [RoleController::class, 'index'])->name('roles.index');
//    Route::post('roles/create/{role?}', [RoleController::class, 'store'])->name('roles.store');
//    Route::get('roles/show/{role}', [RoleController::class, 'show'])->name('roles.show');
//    Route::post('roles/{role}/update', [RoleController::class, 'update'])->name('roles.update');
//    Route::delete('roles/{role}/delete', [RoleController::class, 'destroy'])->name('roles.destroy');



//    Route::get('posts/index/{role?}', [PostController::class, 'index'])->name('posts.index');
//    Route::post('posts/create/{role?}', [PostController::class, 'store'])->name('posts.store');
//    Route::get('posts/show/{role}', [PostController::class, 'show'])->name('posts.show');
//    Route::post('posts/{role}/update', [PostController::class, 'update'])->name('posts.update');
//    Route::delete('posts/{role}/delete', [PostController::class, 'destroy'])->name('posts.destroy');


    // Route::get('questions/index/{question?}', [QuestionController::class, 'index'])->name('questions.index');
    // Route::post('questions/create', [QuestionController::class, 'store'])->name('questions.store');
    // Route::get('questions/show/{question}', [QuestionController::class, 'show'])->name('questions.show');
    // Route::post('questions/{question}/update', [QuestionController::class, 'update'])->name('questions.update');
    // Route::delete('questions/{question}/delete', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // Route::get('options/index/{option?}', [OptionController::class, 'index'])->name('options.index');
    // Route::post('options/create', [OptionController::class, 'store'])->name('options.store');
    // Route::get('options/show/{option}', [OptionController::class, 'show'])->name('options.show');
    // Route::post('options/{option}/update', [OptionController::class, 'update'])->name('options.update');
    // Route::delete('options/{option}/delete', [OptionController::class, 'destroy'])->name('options.destroy');

    // Route::get('answers/index/{answer?}', [AnswerController::class, 'index'])->name('answers.index');
    // Route::post('answers/create', [AnswerController::class, 'store'])->name('answers.store');
    // Route::get('answers/show/{answer}', [AnswerController::class, 'show'])->name('answers.show');
    // Route::post('answers/{answer}/update', [AnswerController::class, 'update'])->name('answers.update');
    // Route::delete('answers/{answer}/delete', [AnswerController::class, 'destroy'])->name('answers.destroy');

    // Route::get('reviews/index/{review?}', [ReviewController::class, 'index'])->name('reviews.index');
    // Route::post('reviews/create', [ReviewController::class, 'store'])->name('reviews.store');
    // Route::get('reviews/show/{review}', [ReviewController::class, 'show'])->name('reviews.show');
    // Route::post('reviews/{review}/update', [ReviewController::class, 'update'])->name('reviews.update');
    // Route::delete('reviews/{review}/delete', [ReviewController::class, 'destroy'])->name('reviews.destroy');

//    Route::get('reservations/index/{reservation?}', [ReservationController::class, 'index'])->name('reservations.index');
//    Route::post('reservations/create', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('reservations/show/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('reservations/{reservation}/update', [ReservationController::class, 'update'])->name('reservations.update');
    Route::delete('reservations/{reservation}/delete', [ReservationController::class, 'destroy'])->name('reservations.destroy');


    Route::get('appointments/panel/index/{appointment?}', [AppointmentController::class, 'panelIndex'])->name('appointments.index');
//    Route::middleware('auth:api')->post('appointments/create', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('appointments/show/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::post('appointments/{appointment}/update', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('appointments/{appointment}/delete', [AppointmentController::class, 'destroy'])->name('appointments.destroy');


    Route::get('settings/index/{setting?}', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings/create', [SettingController::class, 'store'])->name('settings.store');
    Route::get('settings/show/{setting}', [SettingController::class, 'show'])->name('settings.show');
    Route::post('settings/{setting}/update', [SettingController::class, 'update'])->name('settings.update');
    Route::delete('settings/{setting}/delete', [SettingController::class, 'destroy'])->name('settings.destroy');
    Route::get('dashboard/admin', [SettingController::class, 'getAdminDashboard'])->name('dashboard.admin');



    Route::get('banks/index/{bank?}', [BankController::class, 'index'])->name('banks.index');
    Route::post('banks/create', [BankController::class, 'store'])->name('banks.store');
    Route::get('banks/show/{bank}', [BankController::class, 'show'])->name('banks.show');
    Route::post('banks/{bank}/update', [BankController::class, 'update'])->name('banks.update');
    Route::delete('banks/{bank}/delete', [BankController::class, 'destroy'])->name('banks.destroy');


    Route::get('medical/records/index/{medicalRecord?}', [MedicalRecordController::class, 'index'])->name('medical.records.index');
    Route::post('medical/records/create', [MedicalRecordController::class, 'store'])->name('medical.records.store');
    Route::get('medical/records/show/{medicalRecord}', [MedicalRecordController::class, 'show'])->name('medical.records.show');
    Route::post('medical/records/{medicalRecord}/update', [MedicalRecordController::class, 'update'])->name('medical.records.update');
    Route::delete('medical/records/{medicalRecord}/delete', [MedicalRecordController::class, 'destroy'])->name('medical.records.destroy');

    Route::get('assets/index/{asset?}', [AssetController::class, 'index'])->name('assets.index');
    Route::post('assets/create', [AssetController::class, 'store'])->name('assets.store');
    Route::get('assets/show/{asset}', [AssetController::class, 'show'])->name('assets.show');
    Route::post('assets/{asset}/update', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('assets/{asset}/delete', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::post('assets/{asset}/payed', [AssetController::class, 'payed'])->name('assets.payed');

    Route::get('categories/index/{category?}', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('categories/create', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('categories/show/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::post('categories/{category}/update', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}/delete', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('zoom/signature', [UserController::class, 'zoomSignature'])->name('payments.index');

//    Route::get('payments/index/{payment?}', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('payments/create', [PaymentController::class, 'store'])->name('payments.store');
    Route::get('payments/show/{token}', [PaymentController::class, 'show'])->name('payments.show');
    Route::post('payments/{payment}/update', [PaymentController::class, 'update'])->name('payments.update');
    Route::delete('payments/{payment}/delete', [PaymentController::class, 'destroy'])->name('payments.destroy');

    Route::post('create/room', [ReservationController::class, 'createRoom'])->name('create.room');
     Route::post('create/room/meeting', [ReservationController::class, 'createMeetingRoom'])->name('create.meeting');

    Route::post('join/room', [ReservationController::class, 'joinRoom'])->name('join.room');
     Route::post('join/direct', [ReservationController::class, 'joinDirectRoom'])->name('join.direct');
    Route::post('room/vote/{appointment}', [ReservationController::class, 'voteRoom'])->name('vote.room');


});

// Route::get('create-transaction', [PayPalController::class, 'createTransaction'])->name('createTransaction');

// Route::get('success-transaction', [PayPalController::class, 'successTransaction'])->name('successTransaction');
// Route::get('cancel-transaction', [PayPalController::class, 'cancelTransaction'])->name('cancelTransaction');



