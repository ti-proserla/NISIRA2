<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class BoletasPagoController extends Controller
{
    public function index(Request $request){
        $codigo=$request->codigo;
        $lista=DB::connection('sqlsrv')
                ->select(DB::raw("SET DATEFIRST 1;
                SELECT	MP.TIPO tipo,
                                -- MP.PERIODO periodo,
                                MP.IDPLANILLA idplanilla,
                                MAX(MP.SEMANA),
                                MAX(CASE WHEN PL.TIPO_ENVIO = 'S'  THEN DATEPART(ISO_WEEK,PP.FECHA_INI) ELSE MP.SEMANA END) semana,
                                PL.TIPO_ENVIO envio,
                                SUM(M.importemof) monto,
                                -- GROUP_CONCAT_S(MP.IDMOVIMIENTO),
                                 --STUFF((	SELECT ', ' + MP2.IDMOVIMIENTO AS [text()]
                                        -- 	FROM movimiento_planilla MP2
                                        -- 	WHERE MP2.IDMOVIMIENTO=MP.IDMOVIMIENTO
                                        -- 	FOR XML PATH('')), 1, 2, '')
                                  -- movimiento,
                                SUBSTRING(MP.PERIODO, 1, 4) anio,
                                DATEPART(ISO_WEEK,PP.FECHA_INI) semana_real
                                -- PP.FECHA_INI
                from movimiento_planilla MP
                INNER JOIN PLANILLA PL ON PL.IDPLANILLA=MP.IDPLANILLA
                INNER JOIN PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO AND PP.SEMANA=MP.SEMANA AND MP.IDPLANILLA=PP.IDPLANILLA
                INNER JOIN COBRARPAGARDOC C ON MP.IDMOVIMIENTO=C.idmovplanilla
                INNER JOIN MOVCTACTE M ON M.IDEMPRESA=C.IDEMPRESA AND M.IDREFERENCIA=C.IDCOBRARPAGARDOC
                where IDCODIGOGENERAL = '77382978'
                AND MP.TIPO='N'
                AND M.tabla='INGRESOEGRESOCABA' 
                AND M.factor=-1
                GROUP BY MP.TIPO, MP.IDPLANILLA, PL.TIPO_ENVIO, SUBSTRING(MP.PERIODO, 1, 4), DATEPART(ISO_WEEK,PP.FECHA_INI)"),
                [$codigo]);
        return response()->json($lista);
    }
    public function show(Request $request){
        $codigo=$request->codigo;

        //sueldo
        $sueldo=DB::connection('sqlsrv')
                ->select("select D.CALCULO, D.VALOR, C.DESCR_CORTA, C.IDTIPOCONCEPTO , C.ORDEN from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento = ?
                AND C.DESCR_CORTA='BASICO'
                AND IDTIPOCONCEPTO='IN'",
                [$codigo])[0]->VALOR;
        // dd($request->all(),$sueldo);

        //REMUNERACIONES
        $ingresos=DB::connection('sqlsrv')
                ->select("select D.CALCULO, D.VALOR, C.DESCR_CORTA, C.IDTIPOCONCEPTO , C.ORDEN 
                from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento = ?
                AND IDTIPOCONCEPTO='IN'",
                [$codigo]);
        //DESCUENTOS
        $descuentos=DB::connection('sqlsrv')
                ->select("select D.CALCULO, D.VALOR, C.DESCR_CORTA, C.IDTIPOCONCEPTO , C.ORDEN from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento = ?
                AND IDTIPOCONCEPTO='DE'",
                [$codigo]);
        //SEGURO
        $seguro=DB::connection('sqlsrv')
                ->select("select D.CALCULO, D.VALOR, C.DESCR_CORTA, C.IDTIPOCONCEPTO , C.ORDEN from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento = ?
                AND IDTIPOCONCEPTO='AE'",
                [$codigo]);
        //tiempos
        $tiempos=DB::connection('sqlsrv')
                ->select("select D.CALCULO, D.VALOR, C.DESCR_CORTA, C.IDTIPOCONCEPTO , C.ORDEN from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento = ?
                AND IDTIPOCONCEPTO='TI'",
                [$codigo]);
        //totales
        $totales=DB::connection('sqlsrv')
                ->select("select D.CALCULO, D.VALOR, C.DESCR_CORTA, C.IDTIPOCONCEPTO , C.ORDEN from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento = ?
                AND IDTIPOCONCEPTO='TO'",
                [$codigo]);

        $periodo=DB::connection('sqlsrv')
                ->select("SELECT 
                                PP.PERIODO, 
                                PP.SEMANA, 
                                FORMAT(PP.FECHA_INI,'dd/MM/yyyy ') FECHA_INI, 
                                FORMAT(PP.FECHA_FIN,'dd/MM/yyyy ') FECHA_FIN 
                        FROM MOVIMIENTO_PLANILLA MP
                        INNER JOIN PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO AND PP.SEMANA = MP.SEMANA
                        where idmovimiento = ?",
                        [$codigo])[0];
        

        $lista=[
            "sueldo"=> $sueldo,
            "ingresos" => $ingresos,
            "descuentos" => $descuentos,
            "seguro" => $seguro,
            "tiempos" => $tiempos,
            "totales" => $totales,
            "periodo" => $periodo
        ];


        $data = [
                'titulo' => 'Styde.net'
            ];
        
        return PDF::loadView('boleta', $lista)
                ->stream('archivo.pdf');
        return view('boleta',compact('periodo','sueldo','ingresos','descuentos','seguro','tiempos','totales'));
        // return response()->json($lista);
    }
}
