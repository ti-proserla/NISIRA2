<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BoletasPagoController extends Controller
{
    public function index(Request $request){
        $codigo=$request->codigo;
        $lista=DB::connection('sqlsrv')
                ->select(DB::raw("SELECT MP.TIPO tipo,MP.PERIODO periodo,MP.IDPLANILLA idplanilla, MP.SEMANA semana, M.importemof monto, MP.IDMOVIMIENTO movimiento 
                from movimiento_planilla MP
                INNER JOIN COBRARPAGARDOC C ON MP.IDMOVIMIENTO=C.idmovplanilla
                INNER JOIN MOVCTACTE M ON M.IDEMPRESA=C.IDEMPRESA AND M.IDREFERENCIA=C.IDCOBRARPAGARDOC
                where IDCODIGOGENERAL = ?
                AND MP.TIPO='N'
                AND M.tabla='INGRESOEGRESOCABA' 
                AND M.factor=-1
                ORDER BY MP.PERIODO DESC,SEMANA DESC"),
                [$codigo]);
        return response()->json($lista);
    }
    public function show(Request $request){
        $codigo=$request->codigo;
        // dd($codigo);

        //sueldo
        $sueldo=DB::connection('sqlsrv')
                ->select("select D.CALCULO, D.VALOR, C.DESCR_CORTA, C.IDTIPOCONCEPTO , C.ORDEN from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento = ?
                AND C.DESCR_CORTA='BASICO'
                AND IDTIPOCONCEPTO='IN'",
                [$codigo])[0]->VALOR;
        // dd($sueldo);
        //REMUNERACIONES
        $ingresos=DB::connection('sqlsrv')
                ->select("select D.CALCULO, D.VALOR, C.DESCR_CORTA, C.IDTIPOCONCEPTO , C.ORDEN from deta_movimiento_planilla D
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
                ->select("SELECT PP.PERIODO, PP.SEMANA, CONVERT(varchar,PP.FECHA_INI,1) FECHA_INI, CONVERT(varchar,PP.FECHA_FIN,1) FECHA_FIN FROM MOVIMIENTO_PLANILLA MP
                INNER JOIN PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO AND PP.SEMANA = MP.SEMANA
                where idmovimiento = ? AND PP.IDPLANILLA ='OBR'",
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

        return view('boleta',compact('periodo','sueldo','ingresos','descuentos','seguro','tiempos','totales'));
        // return response()->json($lista);
    }
}
