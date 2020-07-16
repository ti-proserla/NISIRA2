<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::group(['middleware' => ['cors']], function () {
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
Route::get('edt','AprobacionController@edt');
Route::get('edt/pendiente','AprobacionController@pendientes');
Route::get('edt/stock','AprobacionController@stock');
Route::get('edt/detalle','AprobacionController@detalles');
Route::post('edt/aprobar','AprobacionController@aprobar');
Route::post('edt/login','AprobacionController@login');
// });
