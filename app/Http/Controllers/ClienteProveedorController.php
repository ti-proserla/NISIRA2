<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Model\CostoAsignado;

class ClienteProveedorController extends Controller
{
    public function index(Request $request){

        $query="SELECT idclieprov,razon_social 
                FROM CLIEPROV where CONCAT(idclieprov,' ',razon_social) 
                like CONCAT('%',?,'%')";
        $empresa=$request->empresa;
        $sql_base="";
        switch ($empresa) {
            case '01':
                $sql_base="sqlsrv_proserla";
                break;
            case '02':
                $sql_base="sqlsrv_jayanca";
                break;
            
            default:
                # code...
                break;
        }
        $proveedor=DB::connection($sql_base)
                        ->select(DB::raw($query),[$request->search]);
        return response()->json($proveedor);
    }

    public function show($idclieprov,Request $request){
        $query="SELECT top 1 idclieprov,razon_social FROM CLIEPROV where IDCLIEPROV=?";
        $proveedor=DB::connection('sqlsrv')
                        ->select(DB::raw($query),[$idclieprov]);
        return response()->json($proveedor[0]);        
    }
}