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
        $sqlsrv_empresa='';
        if ($request->empresa=='01') {
                $sqlsrv_empresa="sqlsrv_proserla";
        }
        if ($request->empresa=='02') {
                $sqlsrv_empresa="sqlsrv_jayanca";
        }
        $lista=DB::connection($sqlsrv_empresa)
                ->select(DB::raw("SET DATEFIRST 1;".
                "SELECT	MP.TIPO tipo,
                        MP.IDPLANILLA idplanilla,
                        MIN(MP.PERIODO) min_periodo,
                        MIN(MP.SEMANA) min_semana,
                        MAX(MP.PERIODO) max_periodo,
                        MAX(MP.SEMANA) max_semana,
                        SUBSTRING(MP.PERIODO, 1, 4) anio,
                        MAX(CASE 
                                WHEN PL.TIPO_ENVIO = 'S' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                WHEN PL.TIPO_ENVIO = 'Q' THEN MP.SEMANA 
                                ELSE SUBSTRING(MP.PERIODO, 5, 2) END)
                         semana,
                        RTRIM(PL.TIPO_ENVIO) envio,
                        CONCAT(MAX(MP.IDMOVIMIENTO),',',MIN(MP.IDMOVIMIENTO)) movimientos,
                        SUM(M.importemof) monto
                from movimiento_planilla MP
                INNER JOIN PLANILLA PL ON PL.IDPLANILLA=MP.IDPLANILLA
                INNER JOIN PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO AND PP.SEMANA=MP.SEMANA AND MP.IDPLANILLA=PP.IDPLANILLA
                INNER JOIN COBRARPAGARDOC C ON MP.IDMOVIMIENTO=C.idmovplanilla
                INNER JOIN MOVCTACTE M ON M.IDEMPRESA=C.IDEMPRESA AND M.IDREFERENCIA=C.IDCOBRARPAGARDOC
                where IDCODIGOGENERAL = ?
                AND MP.TIPO='N'
                AND M.tabla='INGRESOEGRESOCABA' 
                AND M.factor=-1
                GROUP BY MP.TIPO, MP.IDPLANILLA, PL.TIPO_ENVIO, SUBSTRING(MP.PERIODO, 1, 4), DATEPART(ISO_WEEK,PP.FECHA_INI)
                ORDER BY min_periodo DESC, min_semana DESC"),
                [$codigo]);
        return response()->json($lista);
    }
    public function show(Request $request){
        $codigo=$request->codigo;
        $arrayCodigos=explode(',',$codigo);
        $sCodigo= (count($arrayCodigos)==1) ? "?" : "?,?" ;        

        if ($request->empresa=='01') {
                $sqlsrv_empresa="sqlsrv_proserla";
                $empresa= [
                        "nombre_empresa" => "PROMOTORA Y SERVICIOS LAMBAYEQUE SAC",
                        "direccion"=> "CAL. ANTOLIN FLORES NRO. 1580 C.P. VILLA SAN JUAN (CARRETERA PANAMERICANA NORTE KM 37)
                        ",
                        "ruc" => "20479813877"
                ]; 
        }
        if ($request->empresa=='02') {
                $sqlsrv_empresa="sqlsrv_jayanca";
                $empresa= [
                        "nombre_empresa" => "JAYANCA FRUITS S.A.C.",
                        "direccion"=> "CAL. ANTOLIN FLORES NRO. 1580 C.P. VILLA SAN JUAN (CARRETERA PANAMERICANA NORTE KM 37)
                        ",
                        "ruc" => "20561338281"
                ]; 
        }

        //sueldo
        $datos=DB::connection($sqlsrv_empresa)
                ->select(
                "SELECT TOP 1
                        PG.IDCODIGOGENERAL CODIGO,
                        CASE WHEN MP.IDAFP is NULL  THEN 'O.N.P' ELSE  A.DESCRIPCION END 
                        SPP,
                        CASE WHEN MP.IDAFP is NULL  THEN '-' ELSE PG.AUTOGENERADOAFP END 
                        COD_SPP,
                        FORMAT(PE.FECHA_INICIOPLANILLA,'dd/MM/yyyy') INICIO_PLANILLA,
                        PG.A_MATERNO,
                        PG.A_PATERNO,
                        PG.NOMBRES, 
                        D.VALOR BASICO, 
                        C.DESCR_CORTA, 
                        C.IDTIPOCONCEPTO, 
                        C.ORDEN 
                FROM deta_movimiento_planilla D
                INNER JOIN MOVIMIENTO_PLANILLA MP ON MP.IDMOVIMIENTO= D.IDMOVIMIENTO
                INNER JOIN PERSONAL_GENERAL PG ON PG.IDCODIGOGENERAL=MP.IDCODIGOGENERAL
                INNER JOIN PERSONAL PE ON PE.IDCODIGOGENERAL=MP.IDCODIGOGENERAL
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                LEFT JOIN AFPS A ON A.IDAFP=MP.IDAFP
                WHERE MP.idmovimiento IN ($sCodigo)
                AND C.DESCR_CORTA='BASICO'
                AND IDTIPOCONCEPTO='IN'
                -- AND MP.fecha_proceso > PE.FECHA_INICIOPLANILLA
                ORDER BY FECHA_INICIOPLANILLA DESC",
                $arrayCodigos)[0];
        //REMUNERACIONES
        $ingresos=DB::connection($sqlsrv_empresa)
                ->select("SELECT SUM(D.CALCULO) CALCULO, C.DESCR_CORTA, C.IDTIPOCONCEPTO 
                from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento IN ($sCodigo)
                AND IDTIPOCONCEPTO='IN'
                GROUP BY C.DESCR_CORTA, D.IDCONCEPTO, C.IDTIPOCONCEPTO
                ORDER BY D.IDCONCEPTO ASC",
                $arrayCodigos);
        //DESCUENTOS
        $descuentos=DB::connection($sqlsrv_empresa)
                ->select("SELECT SUM(D.CALCULO) CALCULO, C.DESCR_CORTA, C.IDTIPOCONCEPTO from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento IN ($sCodigo)
                AND IDTIPOCONCEPTO='DE'
                GROUP BY C.DESCR_CORTA, D.IDCONCEPTO, C.IDTIPOCONCEPTO
                ORDER BY D.IDCONCEPTO ASC",
                $arrayCodigos);
        //SEGURO
        $seguro=DB::connection($sqlsrv_empresa)
                ->select("SELECT SUM(D.CALCULO) CALCULO, C.DESCR_CORTA, C.IDTIPOCONCEPTO from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento IN ($sCodigo)
                AND IDTIPOCONCEPTO='AE'
                GROUP BY C.DESCR_CORTA, D.IDCONCEPTO, C.IDTIPOCONCEPTO
                ORDER BY D.IDCONCEPTO ASC",
                $arrayCodigos);
        //tiempos
        $tiempos=DB::connection($sqlsrv_empresa)
                ->select("SELECT SUM(D.CALCULO) CALCULO, C.DESCR_CORTA, C.IDTIPOCONCEPTO from deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento IN ($sCodigo)
                AND IDTIPOCONCEPTO='TI'
                GROUP BY C.DESCR_CORTA, D.IDCONCEPTO, C.IDTIPOCONCEPTO
                ORDER BY D.IDCONCEPTO ASC",
                $arrayCodigos);
        //totales
        $temp_totales=DB::connection($sqlsrv_empresa)
                ->select("SELECT SUM(D.CALCULO) CALCULO, 
                                C.DESCR_CORTA, 
                                C.IDTIPOCONCEPTO 
                FROM deta_movimiento_planilla D
                INNER JOIN CONCEPTOS C ON C.IDCONCEPTO = D.IDCONCEPTO
                WHERE idmovimiento IN ($sCodigo)
                AND IDTIPOCONCEPTO='TO'
                GROUP BY C.DESCR_CORTA, D.IDCONCEPTO, C.IDTIPOCONCEPTO
                ORDER BY D.IDCONCEPTO ASC",
                $arrayCodigos);
        $totales=[];
        foreach($temp_totales as $total){
                $totales[str_replace(' ','_',$total->DESCR_CORTA)]=$total->CALCULO;
        }

        $periodo=DB::connection($sqlsrv_empresa)
                ->select("SET DATEFIRST 1;".
                        "SELECT 
                                MP.IDCODIGOGENERAL,
                                RTRIM(PL.TIPO_ENVIO) ENVIO,
                                SUBSTRING(MP.PERIODO, 1, 4) anio,
                                MAX(CASE 
                                        WHEN PL.TIPO_ENVIO = 'S' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                        WHEN PL.TIPO_ENVIO = 'Q' THEN MP.SEMANA 
                                        ELSE SUBSTRING(MP.PERIODO, 5, 2) END) semana,
                                FORMAT(MIN(PP.FECHA_INI),'dd/MM/yyyy') FECHA_INI, 
                                FORMAT(MAX(PP.FECHA_FIN),'dd/MM/yyyy') FECHA_FIN 
                        FROM MOVIMIENTO_PLANILLA MP
                        INNER JOIN PLANILLA PL ON PL.IDPLANILLA=MP.IDPLANILLA
                        INNER JOIN PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO AND PP.SEMANA = MP.SEMANA
                        where idmovimiento IN ($sCodigo)
                        GROUP BY MP.IDCODIGOGENERAL,PL.TIPO_ENVIO,SUBSTRING(MP.PERIODO, 1, 4)  ",
                        $arrayCodigos)[0];
     
        $lista=[
                "empresa"=> $empresa,
                "datos"=> $datos,
                "ingresos" => $ingresos,
                "descuentos" => $descuentos,
                "seguro" => $seguro,
                "tiempos" => $tiempos,
                "totales" => $totales,
                "periodo" => $periodo
        ];
        
        return PDF::loadView('boleta', $lista)
                ->download('boleta_pago.pdf');
    }
}
