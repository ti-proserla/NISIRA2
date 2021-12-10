<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Model\CostoAsignado;

class ClienteProveedorController extends Controller
{
    public function index(Request $request){

        $query="";

// '20602601286'
        $proveedor=DB::connection('sqlsrv')
                        ->select(DB::raw($query),[$request->idclieprov]);
        return response()->json($proveedor);
    }

    public function show($idclieprov,Request $request){
        $query="SELECT top 1 idclieprov,razon_social FROM CLIEPROV where IDCLIEPROV=?";
        $proveedor=DB::connection('sqlsrv')
                        ->select(DB::raw($query),[$idclieprov]);
        return response()->json($proveedor[0]);        
    }
}