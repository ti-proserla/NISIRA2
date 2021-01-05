<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Peru\Http\ContextClient;
use Peru\Jne\{Dni, DniParser};
use App\Model\CuentaTrabajador;

class CuentaTrabajadorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $codigo=$request->codigo;
        $fecha_nacimiento=$request->fecha_nacimiento;
        $empresa=$request->empresa;
        switch ($empresa) {
            case '01':
                $sqlsrv_empresa="sqlsrv_proserla";
                break;
            case '02':
                $sqlsrv_empresa="sqlsrv_jayanca";
                break;
        }

        $lista=DB::connection($sqlsrv_empresa)
                ->select(DB::raw("SELECT * 
                                FROM PERSONAL_GENERAL WHERE IDCODIGOGENERAL=? AND FECHA_NACIMIENTO=?"),
                [$codigo,$fecha_nacimiento]);
        if (count($lista)==0) {
            
        }
        dd($lista);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         * Seleccionar empresa
         */
        $empresa=$request->empresa;
        switch ($empresa) {
            case '01':
                $sqlsrv_empresa="sqlsrv_proserla";
                break;
            case '02':
                $sqlsrv_empresa="sqlsrv_jayanca";
                break;
        }

        $planilla=$request->planilla;
        switch ($planilla) {
            case 'ADM':
                $codigo=$request->codigo;
                $password=$request->password;
                
                $lista=DB::connection($sqlsrv_empresa)
                        ->select(DB::raw("SELECT    PG.IDCODIGOGENERAL codigo,
                                                    PG.A_MATERNO a_materno,
                                                    PG.A_PATERNO a_paterno,
                                                    PG.NOMBRES nombres,
                                                    '$planilla' planilla,
                                                    '$empresa' empresa 
                                        FROM PERSONAL_GENERAL PG
                                        INNER JOIN PERSONAL P ON  P.IDCODIGOGENERAL=PG.IDCODIGOGENERAL
                                        WHERE PG.IDCODIGOGENERAL=? 
                                        AND (P.IDPLANILLA='FIJ'  
                                        OR P.IDPLANILLA='ADM')"),
                        [$codigo]);
                if (count($lista)==0) {
                    return response()->json([
                        "status"    =>"ERROR",
                        "message"   =>"No existe en la Planilla."
                    ]);
                }else{

                    $cuenta=CuentaTrabajador::where('CODIGO',$codigo)
                        ->first();
                    if ($cuenta==null) {
                        $cuenta=new CuentaTrabajador();
                        $cuenta->CODIGO =$lista[0]->codigo;  
                        $cuenta->A_MATERNO =$lista[0]->a_materno;  
                        $cuenta->A_PATERNO =$lista[0]->a_paterno;  
                        $cuenta->NOMBRES =$lista[0]->nombres;  
                        $cuenta->PASSWORD ="";
                        $cuenta->save();  
                    }
                    
                    if ($cuenta->PASSWORD==$password) {
                        return response()->json([
                                "status"    =>"OK",
                                "message"   =>"Personal encontrado.",
                                "data"      =>$lista[0]
                            ]);
                    }else {
                        return response()->json([
                            "status"    =>"ERROR",
                            "message"   =>"Constraseña incorrecta."
                            ]);
                    }   
                }
                break;
            
            default:
                $codigo=$request->codigo;
                $fecha_nacimiento=$request->fecha_nacimiento;
                $lista=DB::connection($sqlsrv_empresa)
                        ->select(DB::raw("SELECT    PG.IDCODIGOGENERAL codigo,
                                                    PG.A_MATERNO a_materno,
                                                    PG.A_PATERNO a_paterno,
                                                    PG.NOMBRES nombres,
                                                    '$planilla' planilla,
                                                    '$empresa' empresa 
                                        FROM PERSONAL_GENERAL PG
                                        INNER JOIN PERSONAL P ON  P.IDCODIGOGENERAL=PG.IDCODIGOGENERAL
                                        WHERE PG.IDCODIGOGENERAL=? 
                                        AND PG.FECHA_NACIMIENTO=? 
                                        AND P.IDPLANILLA<>'FIJ' 
                                        AND P.IDPLANILLA<>'ADM'"),
                        [$codigo,$fecha_nacimiento]);
                if (count($lista)==0) {
                    return response()->json([
                        "status"    =>"ERROR",
                        "message"   =>"Datos Incorrectos."
                    ]);
                }else{
                    return response()->json([
                        "status"    =>"OK",
                        "message"   =>"Personal encontrado.",
                        "data"      =>$lista[0]
        
                    ]);
                }
                break;
        }   
    }

    public function show($id)
    {
        
    }

    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $codigo)
    {
        // dd($request->all());
        // $codigo=$request->codigo;
        $password=$request->password;
        $cuenta=CuentaTrabajador::where('CODIGO',$codigo)
            ->first();
        $cuenta->password=$password;
        $cuenta->save();
        return json_encode([
                    "status" => "OK",
                    "data"   => "Contraseña Actualizada"
                ]);
    }
}
