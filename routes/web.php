<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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
    $description = serialize([
        'event' => 'Enter Api',
        'input' => $request->ip(),
        'header' => $request->header('user-agent')
    ]);
    $agent = new \Jenssegers\Agent\Agent;

    dd($agent);
    activity()->log($description);
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
