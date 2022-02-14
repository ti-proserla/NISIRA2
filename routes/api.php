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
Route::group(['middleware' => ['cors']], function () {
    Route::get('SeguimientoDocumentario/status','SeguimientoDocumentarioController@status');
    Route::resource('cuenta_trabajador', 'CuentaTrabajadorController');
    Route::post('bp/boletas','BoletasPagoController@index');
    Route::get('bp/boletas/show','BoletasPagoController@show');
    Route::get('bp/boletas/modulo_cajero','BoletasPagoController@modulo_cajero');

    // if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    //     header('Access-Control-Allow-Methods: GET, POST, DELETE');
    //     header('Access-Control-Allow-Headers: Authorization');
    //     http_response_code(204);
    // }

    Route::get('edt','AprobacionController@edt');
    Route::get('edt/pendiente','AprobacionController@pendientes');
    Route::get('edt/stock','AprobacionController@stock');
    Route::get('edt/detalle','AprobacionController@detalles');
    Route::post('edt/aprobar','AprobacionController@aprobar');
    Route::post('edt/login','AprobacionController@login');
});
Route::get('costos','SeguimientoDocumentarioController@costos');
Route::post('CostoAsignado','SeguimientoDocumentarioController@costo_asignado');
Route::post('CostoAsignado/recepcion','SeguimientoDocumentarioController@costo_asignado_recepcion');



Route::get('licencias', 'LicenciasController@index');

//Panel Nisira Conect
Route::post('login','CuentaController@login');
Route::get('rutas','CuentaController@rutas');
Route::resource('cliente-proveedor','ClienteProveedorController');

Route::get('SeguimientoDocumentario','SeguimientoDocumentarioController@index');
Route::get('SeguimientoDocumentario/Recepcion','SeguimientoDocumentarioController@recepcion');