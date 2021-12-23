<?php

namespace App\Http\Controllers;

use App\Model\Cuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// use App\Http\Requests\CuentaNuevo;
// use App\Http\Requests\CuentaEditar;
use Carbon\Carbon;

class CuentaController extends Controller
{
    /**
     * Visualiza todos los cuentaes
     */
    public function index(Request $request)
    {
        if ($request->search==null||$request->search=="null") {
            $request->search="";
        }
        $cuentaes=Cuenta::where(DB::raw('CONCAT(nombre,apellido)'),'like','%'.$request->search.'%')->paginate(8);
        return response()->json($cuentaes);
    }

    /**
     * Registra un nuevo cuenta
     */
    public function store(CuentaNuevo $request)
    {
        $cuenta=new Cuenta();
        $cuenta->nombre=strtoupper($request->nombre);
        $cuenta->apellido=strtoupper($request->apellido);
        $cuenta->usuario=strtoupper($request->usuario);
        $cuenta->password=strtoupper($request->password);
        $cuenta->api_token='moment';
        $cuenta->estado='0';
        $cuenta->rol_id=$request->rol_id;
        $cuenta->fundo_id=$request->fundo_id;
        $cuenta->save();
        $cuenta->api_token=$cuenta->id.'_'.Carbon::now()->format('YmdHisu');
        $cuenta->save();
        return response()->json([
            "status"=> "OK",
            "data"  => "cuenta Registrado"
        ]);
        
    }
        
        /**
         * Visualiza datos de un solo cuenta
         */
    public function show($id)
    {
        $cuenta=Cuenta::where('id',$id)->first();
        return response()->json($cuenta);
    }
        
    public function update(CuentaEditar $request, $id)
    {
        $cuenta=Cuenta::where('id',$id)->first();
        $cuenta->nombre=strtoupper($request->nombre);
        $cuenta->apellido=strtoupper($request->apellido);
        $cuenta->fundo_id=strtoupper($request->fundo_id);
        $cuenta->rol_id=$request->rol_id;
        $cuenta->save();
        return response()->json([
            "status"=> "OK",
            "data"  => "cuenta Actualizado"
        ]);
    }

    /**
     * Desabilita al cuenta
     */
    public function estado($id)
    {
        $cuenta=Cuenta::where('id',$id)->first();
        $cuenta->estado=($cuenta->estado=='0') ? '1' : '0';
        $cuenta->save();
        return response()->json([
            "status"=> "OK",
            "data"  => "Estado Actualizado"
        ]);
    }

    public function rutas(Request $request){
        $rol=$request->usuario;
        $listaRutas=[];
        // switch ($rol) {
        //     case 'ADMINISTRADOR':
        //         $listaRutas=["/atencion","/empresa","/planilla","/personal","/reporte-personal","/reporte-fecha","/reporte-tiempo","/servicio"];
        //         break;
        //     case 'REPORTEADOR':
        //         $listaRutas=["/reporte-fecha","/reporte-personal"];
        //         break;
        //     case 'TERMINAL':
        //         $listaRutas=["/atencion","/reporte-fecha","/reporte-personal"];
        //         break;
        //     default:
        //         $listaRutas=[];
        //         break;
        // }
        array_push($listaRutas,"/");
        array_push($listaRutas,"/seguimiento-documentario");
        if ($request->usuario=='GSEMINARIO'||$request->usuario=='ADMINISTRADOR') {
            array_push($listaRutas,"/seguimiento-recepcion");
        }
        return response()->json($listaRutas);
    }

    public function login(Request $request){
        $usuario=$request->usuario;
        $password=$request->password;
        $empresa=$request->empresa;
        $password_encrytada="";
        $s1="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@";
        $s2="èïîìÄÅæôÆjöòÿÖÜø£É×ƒáíóúñÑ|°!#$%&/()=?¿<}~Çüéâäàåçêëãª¿®¦ÁÂÀ©¦@";
        for ($i = 0; $i < strlen($password); $i++) {
            $letra_contra = $password[$i];
            for ($j = 0; $j < strlen($s1); $j++) {
                
                $letra_s1 = $s1[$j];
                if ($letra_s1==$letra_contra) {
                    $password_encrytada=$password_encrytada.mb_substr($s2,$j,1);
                    break;
                }
            }
        }
        
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

        $cuenta=DB::connection($sql_base)
                    ->select(DB::raw("select IDUSUARIO usuario from USUARIO where IDUSUARIO=? AND PASSWORD=?"),
                                [$usuario,$password_encrytada]
                            );
        if ($cuenta==null) {
            return response()->json([
                "status"=> "ERROR",
                "data"  => "Usuario o Contraseña incorrecta."
            ]);
        }else{
            
            $cuenta[0]->empresa=$empresa;
            return response()->json([
                "status"=> "OK",
                "data"  => $cuenta[0]
            ]);
        }
    }
}
