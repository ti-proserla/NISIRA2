<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

Use App\Model\HistorialDescargas;

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
                ->select(DB::raw("SELECT MP.TIPO tipo,
                                MP.IDPLANILLA idplanilla,
                                MIN(MP.SEMANA) min_semana,
                                MAX(MP.SEMANA) max_semana,
                                CASE
                                                WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                                                WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                                                ELSE YEAR(PP.FECHA_INI) END anio,
                                MAX(CASE 
                                        WHEN PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                        WHEN PL.TIPO_ENVIO = 'Q' THEN MP.SEMANA 
                                        ELSE SUBSTRING(MP.PERIODO, 5, 2) END)
                                semana,
                                RTRIM(CASE 
                                        WHEN PL.TIPO_ENVIO = 'N'
                                        THEN 'S' ELSE PL.TIPO_ENVIO end) envio,
                                CONCAT(MAX(MP.IDMOVIMIENTO),',',MIN(MP.IDMOVIMIENTO)) movimientos,
                                SUM(M.importemof) monto
                        from movimiento_planilla MP
                        INNER JOIN PLANILLA PL ON PL.IDPLANILLA=MP.IDPLANILLA
                        INNER JOIN PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO_PLANILLA AND PP.SEMANA=MP.SEMANA AND MP.IDPLANILLA=PP.IDPLANILLA
                        INNER JOIN COBRARPAGARDOC C ON MP.IDMOVIMIENTO=C.idmovplanilla
                        INNER JOIN MOVCTACTE M ON M.IDEMPRESA=C.IDEMPRESA AND M.IDREFERENCIA=C.IDCOBRARPAGARDOC
                        where IDCODIGOGENERAL = ?
                        AND MP.TIPO='N'
                        AND M.tabla='INGRESOEGRESOCABA' 
                        AND M.factor=-1
                        GROUP BY MP.TIPO, MP.IDPLANILLA, PL.TIPO_ENVIO, CASE
                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                        ELSE YEAR(PP.FECHA_INI) END, DATEPART(ISO_WEEK,PP.FECHA_INI)
                        ORDER BY anio DESC, semana DESC"),
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
                ->select("SELECT 
                        MP.IDCODIGOGENERAL,
                        CASE WHEN PL.TIPO_ENVIO='N' THEN 'S' ELSE RTRIM(PL.TIPO_ENVIO) END ENVIO,
                        CASE
                                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                                        ELSE YEAR(PP.FECHA_INI) END anio,
                        MAX(CASE 
                                WHEN PL.TIPO_ENVIO = 'S' OR  PL.TIPO_ENVIO = 'N' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                WHEN PL.TIPO_ENVIO = 'Q' THEN MP.SEMANA 
                                ELSE SUBSTRING(MP.PERIODO, 5, 2) END) semana,
                        MIN(FECHA_INI) FECHA_INI_N, 
                        MAX(FECHA_FIN) FECHA_FIN_N, 
                        FORMAT(MIN(PP.FECHA_INI),'dd/MM/yyyy') FECHA_INI, 
                        FORMAT(MAX(PP.FECHA_FIN),'dd/MM/yyyy') FECHA_FIN 
                FROM MOVIMIENTO_PLANILLA MP
                INNER JOIN PLANILLA PL ON PL.IDPLANILLA=MP.IDPLANILLA
                INNER JOIN PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO_PLANILLA AND PP.SEMANA = MP.SEMANA AND MP.IDPLANILLA=PP.IDPLANILLA
                where idmovimiento IN ($sCodigo)
                GROUP BY MP.IDCODIGOGENERAL,PL.TIPO_ENVIO, CASE
                                WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                            WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                                ELSE YEAR(PP.FECHA_INI) END",
                        $arrayCodigos)[0];
        
        $horas_semana=DB::connection($sqlsrv_empresa)
                ->select("SELECT IDCODIGOGENERAL, 
                        SUM(case when DATEPART(WEEKDAY, FECHACREACION ) =1 then TOTAL_HORAS else 0 end ) as lunes,
                        SUM(case when DATEPART(WEEKDAY, FECHACREACION ) =2 then TOTAL_HORAS else 0 end ) as martes,
                        SUM(case when DATEPART(WEEKDAY, FECHACREACION ) =3 then TOTAL_HORAS else 0 end ) as miercoles,
                        SUM(case when DATEPART(WEEKDAY, FECHACREACION ) =4 then TOTAL_HORAS else 0 end ) as jueves,
                        SUM(case when DATEPART(WEEKDAY, FECHACREACION ) =5 then TOTAL_HORAS else 0 end ) as viernes,
                        SUM(case when DATEPART(WEEKDAY, FECHACREACION ) =6 then TOTAL_HORAS else 0 end ) as sabado,
                        SUM(case when DATEPART(WEEKDAY, FECHACREACION ) =7 then TOTAL_HORAS else 0 end ) as domingo
                FROM DET_ASISTENCIA
                WHERE FECHACREACION >= ?
                AND FECHACREACION <= ?
                AND IDCODIGOGENERAL = ?
                GROUP BY IDCODIGOGENERAL",[
                        $periodo->FECHA_INI_N,
                        $periodo->FECHA_FIN_N,
                        $datos->CODIGO
                ]);
        $lista=[
                "empresa"=> $empresa,
                "datos"=> $datos,
                "ingresos" => $ingresos,
                "descuentos" => $descuentos,
                "seguro" => $seguro,
                "tiempos" => $tiempos,
                "totales" => $totales,
                "periodo" => $periodo,
                "horas_semana" => ($horas_semana==null) ? $horas_semana : $horas_semana[0]
        ];

        if ($request->has('data')) {
                return response()
                        ->json($lista);
        }

        if ($request->has('termal_view')) {
                return view('boleta_termica',$lista);
        }

        return PDF::loadView('boleta', $lista)
                ->download('boleta_pago.pdf');
    }

        //Comprobacion de existencia y no duplicidad de las boletas
        public function modulo_cajero(Request $request){
                $codigo_personal=$request->codigo_personal;

                if ($request->empresa=='01') {
                        $sqlsrv_empresa="sqlsrv_proserla";
                        $empresa= [
                                "nombre_empresa" => "PROMOTORA Y SERVICIOS LAMBAYEQUE SAC",
                                "direccion"=> "CAL. ANTOLIN FLORES NRO. 1580 C.P. VILLA SAN JUAN (CARRETERA PANAMERICANA NORTE KM 37)
                                ",
                                "ruc" => "20479813877",
                                "logo"=> "logo.png"
                        ]; 
                }
                if ($request->empresa=='02') {
                        $sqlsrv_empresa="sqlsrv_jayanca";
                        $empresa= [
                                "nombre_empresa" => "JAYANCA FRUITS S.A.C.",
                                "direccion"=> "CAL. ANTOLIN FLORES NRO. 1580 C.P. VILLA SAN JUAN (CARRETERA PANAMERICANA NORTE KM 37)
                                ",
                                "ruc" => "20561338281",
                                "logo"=> "jayanca.png"
                        ]; 
                }
                
                $encontrado=DB::connection($sqlsrv_empresa)
                        ->select("SELECT TOP 1 * FROM 
                        (
                        SELECT MP.IDPLANILLA idplanilla,
                                CASE
                                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                                        ELSE YEAR(PP.FECHA_INI) END anio,
                                MAX(CASE PL.TIPO_ENVIO
                                        WHEN 'N' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                                        WHEN 'S' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                        WHEN 'Q' THEN MP.SEMANA 
                                        ELSE SUBSTRING(MP.PERIODO, 5, 2) END)
                                semana,
                                        MAX(PP.FECHA_FIN) fecha_fin,
                                RTRIM(CASE PL.TIPO_ENVIO
                                        WHEN 'N'
                                        THEN 'S' ELSE PL.TIPO_ENVIO end) envio,
                                CONCAT(MAX(MP.IDMOVIMIENTO),',',MIN(MP.IDMOVIMIENTO)) movimientos,
                                        '01' empresa
                        FROM PROSERLA2020.dbo.movimiento_planilla MP
                        INNER JOIN PROSERLA2020.dbo.PLANILLA PL ON PL.IDPLANILLA=MP.IDPLANILLA
                        INNER JOIN PROSERLA2020.dbo.PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO_PLANILLA AND PP.SEMANA=MP.SEMANA AND MP.IDPLANILLA=PP.IDPLANILLA
                        INNER JOIN PROSERLA2020.dbo.COBRARPAGARDOC C ON MP.IDMOVIMIENTO=C.idmovplanilla
                        INNER JOIN PROSERLA2020.dbo.MOVCTACTE M ON M.IDEMPRESA=C.IDEMPRESA AND M.IDREFERENCIA=C.IDCOBRARPAGARDOC
                        where IDCODIGOGENERAL = ?
                        AND MP.TIPO='N'
                        AND M.factor=-1
                        GROUP BY MP.IDPLANILLA, PL.TIPO_ENVIO, CASE
                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                        ELSE YEAR(PP.FECHA_INI) END, DATEPART(ISO_WEEK,PP.FECHA_INI)
                        HAVING MAX(PP.FECHA_FIN)<GETDATE()
                        
                        
                        UNION 
                        
                        SELECT MP.IDPLANILLA idplanilla,
                                CASE
                                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                                        ELSE YEAR(PP.FECHA_INI) END anio,
                                MAX(CASE PL.TIPO_ENVIO
                                        WHEN 'N' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                                        WHEN 'S' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                        WHEN 'Q' THEN MP.SEMANA 
                                        ELSE SUBSTRING(MP.PERIODO, 5, 2) END)
                                semana,
                                        MAX(PP.FECHA_FIN) fecha_fin,
                                RTRIM(CASE PL.TIPO_ENVIO
                                        WHEN 'N'
                                        THEN 'S' ELSE PL.TIPO_ENVIO end) envio,
                                CONCAT(MAX(MP.IDMOVIMIENTO),',',MIN(MP.IDMOVIMIENTO)) movimientos,
                                        '02' empresa
                        FROM JAYANCA.dbo.movimiento_planilla MP
                        INNER JOIN JAYANCA.dbo.PLANILLA PL ON PL.IDPLANILLA=MP.IDPLANILLA
                        INNER JOIN JAYANCA.dbo.PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO_PLANILLA AND PP.SEMANA=MP.SEMANA AND MP.IDPLANILLA=PP.IDPLANILLA
                        INNER JOIN JAYANCA.dbo.COBRARPAGARDOC C ON MP.IDMOVIMIENTO=C.idmovplanilla
                        INNER JOIN JAYANCA.dbo.MOVCTACTE M ON M.IDEMPRESA=C.IDEMPRESA AND M.IDREFERENCIA=C.IDCOBRARPAGARDOC
                        where IDCODIGOGENERAL = ?
                        AND MP.TIPO='N'
                        AND M.factor=-1
                        GROUP BY MP.IDPLANILLA, PL.TIPO_ENVIO,
                        CASE
                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                        ELSE YEAR(PP.FECHA_INI) END, DATEPART(ISO_WEEK,PP.FECHA_INI)
                        HAVING MAX(PP.FECHA_FIN)<GETDATE()
                        ) TB
                        ORDER BY fecha_fin DESC",[$codigo_personal,$codigo_personal]);
                $encontrado=(count($encontrado)>0) ? $encontrado[0] : null;
                if ($encontrado!=null) {
                        if ($encontrado->idplanilla=='FIJ') {
                                return response()->json([
                                        "status"=>"error",
                                        "message"=>"Boleta no disponible, intente mas tarde."
                                ]);
                        }
                        $historial=HistorialDescargas::where('movimientos',$encontrado->movimientos)->first();
                        if ($historial!=null) {
                                return response()->json([
                                        "status"=>"error",
                                        "message"=>"La boleta ya fue impresa."
                                ]);
                        }
                        

                        $historialDescargas=new HistorialDescargas();
                        $historialDescargas->movimientos=$encontrado->movimientos;
                        $historialDescargas->codigo_personal=$codigo_personal;
                        $historialDescargas->anio=$encontrado->anio;
                        $historialDescargas->semana=$encontrado->semana;
                        $historialDescargas->envio=$encontrado->envio;
                        $historialDescargas->save();
                        $request->empresa=$encontrado->empresa;
                        // return response()->json($this->getData($encontrado->movimientos,$request->empresa));
                        return view('boleta_termica',$this->getData($encontrado->movimientos,$request->empresa));
                }else{
                        return response()->json([
                                "status"=> "error",
                                "message"=> "No se encontro Boleta disponible"
                        ]);
                }


        }

        /**
         * @var codigo idmovimientos separados por "," 
         * @var sqlsrv_empresa
         */
        public function getData($codigos,$cod_empresa){

                if ($cod_empresa=='01') {
                        $sqlsrv_empresa="sqlsrv_proserla";
                        $empresa= [
                                "nombre_empresa" => "PROMOTORA Y SERVICIOS LAMBAYEQUE SAC",
                                "direccion"=> "CAL. ANTOLIN FLORES NRO. 1580 C.P. VILLA SAN JUAN (CARRETERA PANAMERICANA NORTE KM 37)
                                ",
                                "ruc" => "20479813877",
                                "logo"=> "logo.png"
                        ]; 
                }
                if ($cod_empresa=='02') {
                        $sqlsrv_empresa="sqlsrv_jayanca";
                        $empresa= [
                                "nombre_empresa" => "JAYANCA FRUITS S.A.C.",
                                "direccion"=> "CAL. ANTOLIN FLORES NRO. 1580 C.P. VILLA SAN JUAN (CARRETERA PANAMERICANA NORTE KM 37)
                                ",
                                "ruc" => "20561338281",
                                "logo"=> "jayanca.png"
                        ]; 
                }


                $arrayCodigos=explode(',',$codigos);
                $sCodigo= (count($arrayCodigos)==1) ? "?" : "?,?" ;    

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
                                PG.IDBANCO,
                                PG.CUENTA_BANCO, 
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
                        ->select("SELECT 
                                MP.IDCODIGOGENERAL,
                                CASE WHEN PL.TIPO_ENVIO='N' THEN 'S' ELSE RTRIM(PL.TIPO_ENVIO) END ENVIO,
                                CASE
                                                WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                                                WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                                                ELSE YEAR(PP.FECHA_INI) END anio,
                                MAX(CASE 
                                        WHEN PL.TIPO_ENVIO = 'S' OR  PL.TIPO_ENVIO = 'N' THEN DATEPART(ISO_WEEK,PP.FECHA_INI) 
                                        WHEN PL.TIPO_ENVIO = 'Q' THEN MP.SEMANA 
                                        ELSE SUBSTRING(MP.PERIODO, 5, 2) END) semana,
                                MIN(FECHA_INI) FECHA_INI_N, 
                                MAX(FECHA_FIN) FECHA_FIN_N, 
                                FORMAT(MIN(PP.FECHA_INI),'dd/MM/yyyy') FECHA_INI, 
                                FORMAT(MAX(PP.FECHA_FIN),'dd/MM/yyyy') FECHA_FIN 
                        FROM MOVIMIENTO_PLANILLA MP
                        INNER JOIN PLANILLA PL ON PL.IDPLANILLA=MP.IDPLANILLA
                        INNER JOIN PERIODO_PLANILLA PP ON PP.PERIODO=MP.PERIODO_PLANILLA AND PP.SEMANA = MP.SEMANA AND MP.IDPLANILLA=PP.IDPLANILLA
                        where idmovimiento IN ($sCodigo)
                        GROUP BY MP.IDCODIGOGENERAL,PL.TIPO_ENVIO, CASE
                                        WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) > 50 AND MONTH(PP.FECHA_INI) = 1 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) - 1
                                WHEN DATEPART(ISO_WEEK, PP.FECHA_INI) = 1 AND MONTH(PP.FECHA_INI) = 12 AND (PL.TIPO_ENVIO = 'S' OR PL.TIPO_ENVIO = 'N') THEN YEAR(PP.FECHA_INI) + 1
                                        ELSE YEAR(PP.FECHA_INI) END",
                                $arrayCodigos)[0];

                $horas_semana=DB::connection($sqlsrv_empresa)
                        ->select("SELECT IDCODIGOGENERAL, 
                                SUM(case when DATEPART(WEEKDAY, FECHACREACION ) = 2 then TOTAL_HORAS else 0 end ) as lunes,
                                SUM(case when DATEPART(WEEKDAY, FECHACREACION ) = 3 then TOTAL_HORAS else 0 end ) as martes,
                                SUM(case when DATEPART(WEEKDAY, FECHACREACION ) = 4 then TOTAL_HORAS else 0 end ) as miercoles,
                                SUM(case when DATEPART(WEEKDAY, FECHACREACION ) = 5 then TOTAL_HORAS else 0 end ) as jueves,
                                SUM(case when DATEPART(WEEKDAY, FECHACREACION ) = 6 then TOTAL_HORAS else 0 end ) as viernes,
                                SUM(case when DATEPART(WEEKDAY, FECHACREACION ) = 7 then TOTAL_HORAS else 0 end ) as sabado,
                                SUM(case when DATEPART(WEEKDAY, FECHACREACION ) = 1 then TOTAL_HORAS else 0 end ) as domingo,
                                SUM(TOTAL_HORAS) total
                        FROM DET_ASISTENCIA
                        WHERE FECHACREACION >= CAST(? AS date)
                        AND FECHACREACION <= CAST(? AS date)
                        AND IDCODIGOGENERAL = ?
                        GROUP BY IDCODIGOGENERAL",[
                                $periodo->FECHA_INI_N,
                                $periodo->FECHA_FIN_N,
                                $datos->CODIGO
                        ]);

                        
                return [
                                "empresa"=> $empresa,
                                "datos"=> $datos,
                                "ingresos" => $ingresos,
                                "descuentos" => $descuentos,
                                "seguro" => $seguro,
                                "tiempos" => $tiempos,
                                "totales" => $totales,
                                "periodo" => $periodo,
                                "horas_semana" => ($horas_semana==null) ? $horas_semana : $horas_semana[0]
                        ];
        }
}
