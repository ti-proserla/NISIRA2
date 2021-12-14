<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Model\CostoAsignado;

class SeguimientoDocumentarioController extends Controller
{
    public function index(Request $request){

        $query=
        "SELECT	FORMAT(RD.FECHA, 'yyyy-MM-dd') fecha_recepcion,
                FORMAT(DRD.FECHA, 'yyyy-MM-dd') fecha_documento,
                DRD.idrecepcion,
                DRD.item,
                DRD.idclieprov,
                DRD.razon_social,
                DRD.iddocumento,
                CONCAT(DRD.iddocumento,' ',DRD.serie,'-',DRD.numero) documento,
                Round(DRD.importe, 2, 0) importe,
                FORMAT(T1.fechacreacion, 'yyyy-MM-dd') fecha_provision,
                CASE 
                    WHEN M.idmovctacte IS NOT NULL
                    THEN 'PAGADO'
                    ELSE ''
                    END AS tesoreria,
                Round(M.importe, 2, 0) importe_cta,
                FORMAT(M.fecharegistro, 'yyyy-MM-dd') fecha_tesoreria
        FROM RECEPCION_DOCUMENTOS RD
        INNER JOIN drecepcion_documentos DRD ON  RD.IDRECEPCION=DRD.IDRECEPCION

        LEFT JOIN COBRARPAGARDOC T1  
            ON T1.numero=DRD.NUMERO AND T1.serie=DRD.SERIE AND DRD.IDCLIEPROV=T1.idclieprov AND T1.ORIGEN = 'P' 
        LEFT JOIN 
        (	SELECT IDDOCUMENTO 
            FROM CFG_FORM_DOCUMENTO 
            WHERE IDEMPRESA='001' AND CFORM='lst_provision'
        ) TL 
            ON TL.IDDOCUMENTO = T1.IDDOCUMENTO

        LEFT JOIN MOVCTACTE M ON M.IDEMPRESA=T1.IDEMPRESA AND M.IDREFERENCIA=T1.IDCOBRARPAGARDOC and M.factor=-1 AND M.TABLA<>'AJUSTE'
        WHERE DRD.IDCLIEPROV=?
        ORDER BY FECHA_RECEPCION DESC";

// '20602601286'
        $documentos=DB::connection('sqlsrv')
                        ->select(DB::raw($query),[$request->idclieprov]);

        foreach ($documentos as $key => $documento) {
            $ca=CostoAsignado::where('idrecepcion',$documento->idrecepcion )
                                ->where('item', $documento->item)
                                ->where('empresa',$request->empresa)->first();
            if ($ca!=null) {
                $documento->con_ccosto= 'Si';
            }else{
                $documento->con_ccosto= 'No';
            }
        }
        return response()->json($documentos);
    }

    public function costos(){
        $documentos=DB::connection('sqlsrv')
            ->select(DB::raw("SELECT RTRIM(IDCCOSTO) IDCCOSTO,DESCRIPCION FROM CONSUMIDOR"));
        dd($documentos);
    }
    public function costo_asignado(Request $request){
        $costo_asignado=new CostoAsignado();
        $costo_asignado->idrecepcion=$request->idrecepcion;
        $costo_asignado->item=$request->item;
        $costo_asignado->empresa=$request->empresa;
        $costo_asignado->save();
        return response()->json([
            "status"    =>  "OK",
            "message"   =>  "Costo Asignado."
        ]);
    }
}