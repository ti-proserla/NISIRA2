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
                ->select(DB::raw("SELECT    PG.IDCODIGOGENERAL codigo,
                                            PG.A_MATERNO a_materno,
                                            PG.A_PATERNO a_paterno,
                                            PG.NOMBRES nombres,
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
                "message"   =>"Personal no registrado."
            ]);
        }else{
            return response()->json([
                "status"    =>"OK",
                "message"   =>"Personal encontrado.",
                "data"      =>$lista[0]

            ]);
        }
        // $codigo=$request->codigo;
        // $password=$request->password;
        // $verificador=$request->verificador;
        // $empresa=$request->empresa;
        // if ($empresa=='01') {
        //     $sqlsrv_empresa="sqlsrv_proserla"; 
        // }
        // if ($empresa=='02') {
        //     $sqlsrv_empresa="sqlsrv_jayanca";
        // }
        // /**
        //  * Existe trabajador en alguna empresa
        //  */
        // $encontrado=DB::connection($sqlsrv_empresa)
        //         ->select("SELECT TOP 1 * 
        //                 FROM PERSONAL_GENERAL 
        //                 WHERE IDCODIGOGENERAL=?",[
        //                     $codigo
        //                 ]);
        // if (0==count($encontrado)) {
        //     return json_encode([
        //         "status" => "ERROR",
        //         "data"   => "El trabajador no existe en el sistema."
        //     ]); 
        // }

        // /**
        //  * Ya cuenta con contraseña
        //  */
        // $cuenta=CuentaTrabajador::where('CODIGO',$codigo)
        //         ->first();
        // if ($cuenta!=null) {
        //     return json_encode([
        //         "status" => "INFO",
        //         "data"   => "El Trabajador ya se encuentra registrado",
        //     ]); 
        // }
        
        // $cs = new Dni(new ContextClient(), new DniParser());
        // $person = $cs->get($codigo);
        // if (!$person) {
        //     return json_encode([
        //         "status" => "ERROR",
        //         "data"   => "No encontrado"
        //     ]);
        //     // echo 'Not found';
        //     exit();
        // }
        // if ($person->codVerifica==$verificador) {
        //     $cuenta=new CuentaTrabajador();
        //     $cuenta->CODIGO=$codigo;
        //     $cuenta->NOMBRES=$cuentas->nombres;
        //     $cuenta->A_PATERNO=$cuentas->apellidoPaterno;
        //     $cuenta->A_MATERNO=$cuentas->apellidoMaterno;
        //     $cuenta->PASSWORD=$password;
        //     $cuenta->save();
        // }
        // return json_encode([
        //         "status" => "OK",
        //         "data"   => 'Cuenta generada, ingresar.'
        //     ]);        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
