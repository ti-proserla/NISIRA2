<?php

use Illuminate\Support\Facades\Route;

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
Route::domain(env('APP_ENV')=='local' ? '127.0.0.1' : '172.16.1.112')->group(function () {
    Route::get('/hola', function () {
        dd("hola");
    });

});
Route::get('/{any}', function(){
    // return view('welcome');
})->where('any', '.*')->name('home');
