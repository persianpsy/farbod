<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function(Request $request) {
    return   $request->fullUrl();
    $agent = new \Jenssegers\Agent\Agent;
    $description = serialize([
        'event' => 'Enter Api',
        'input' => $request->ip(),
        'header' => $request->header('user-agent'),
        'robot'  => $agent->isRobot(),
        'device' => $agent->device(),
        'browser' => $agent->browser()
    ]);
    activity()->log($description);
    $redis = Redis::connection();


    $redis->set('user_1', json_encode([
            'first_name' => 'FARBOD',
            'last_name' => 'NASIRI'
        ])
    );

    return 'hi';
    // return what you want
})->name('login');

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    // return what you want
});

Route::get('/linkstorage', function ()
{


  Artisan::call('storage:link');

});
