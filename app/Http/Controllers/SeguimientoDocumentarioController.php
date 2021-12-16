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
        "SELECT	FORMAT(RD.FECHA, 'dd/MM/yyyy HH:mm') fecha_recepcion,
                FORMAT(DRD.FECHA, 'dd/MM/yyyy') fecha_documento,
                DRD.idrecepcion,
                MO.descripcion moneda,
                DRD.item,
                DRD.idclieprov,
                DRD.razon_social,
                DRD.iddocumento,
                CONCAT(DRD.iddocumento,' ',DRD.serie,'-',DRD.numero) documento,
                MAX(DRD.importe) importe,
                FORMAT(MAX(T1.fechacreacion), 'dd/MM/yyyy HH:mm') fecha_provision,
                CASE 
                    WHEN LI.iddocumento IS NOT NULL
                        THEN CONCAT(MAX(LI.iddocumento),' ',MAX(LI.serie),'-',MAX(LI.numero))
                    WHEN MAX(M.idmovctacte) IS NOT NULL
                        THEN 'PAGADO'
                
                ELSE ''
                END AS tesoreria,
                CASE 
                    WHEN LI.iddocumento IS NOT NULL
                    THEN MAX(DRD.importe)
                    ELSE SUM(M.importe)
                END
                
                importe_cta,
                CASE 
                    WHEN LI.iddocumento IS NOT NULL
                        THEN FORMAT(LI.fechacreacion, 'dd/MM/yyyy')
                    ELSE FORMAT(MAX(M.fecharegistro), 'dd/MM/yyyy')
                END
                fecha_tesoreria
                
                
                
        FROM RECEPCION_DOCUMENTOS RD
        INNER JOIN drecepcion_documentos DRD ON  RD.IDRECEPCION=DRD.IDRECEPCION
        INNER JOIN MONEDAS MO ON MO.idmoneda=DRD.idmoneda
        LEFT JOIN COBRARPAGARDOC T1  
            ON T1.numero=DRD.NUMERO AND T1.serie=DRD.SERIE AND DRD.IDCLIEPROV=T1.idclieprov AND T1.ORIGEN = 'P' 
        -- LEFT JOIN DINGRESOEGRESOCABA DIE ON DIE.IDREFERENCIA=T1.idcobrarpagardoc
        -- LEFT JOIN INGRESOEGRESOCABA IE ON IE.IDINGRESOEGRESOCABA=DIE.IDINGRESOEGRESOCABA
        LEFT JOIN 
        (	SELECT IDDOCUMENTO 
            FROM CFG_FORM_DOCUMENTO 
            WHERE IDEMPRESA='001' AND CFORM='lst_provision'
        ) TL 
            ON TL.IDDOCUMENTO = T1.IDDOCUMENTO

        LEFT JOIN MOVCTACTE M ON M.IDEMPRESA=T1.IDEMPRESA AND M.IDREFERENCIA=T1.IDCOBRARPAGARDOC and M.factor=-1 AND M.TABLA<>'AJUSTE'
        LEFT JOIN DLIQUIDACIONGASTO DL ON DL.iddocumento=DRD.iddocumento AND DL.idclieprov=DRD.IDCLIEPROV AND DL.numero=DRD.NUMERO 
        LEFT JOIN COBRARPAGARDOC LI ON LI.idcobrarpagardoc=DL.idcobrarpagardoc 
        WHERE DRD.IDCLIEPROV=?
        GROUP BY RD.FECHA,DRD.FECHA, 
                DRD.idrecepcion, 
                DRD.item,
                DRD.idclieprov,
                DRD.razon_social,
                DRD.iddocumento,
                DRD.iddocumento,
                DRD.serie,
                DRD.numero,
                LI.iddocumento,
                LI.numero,
                LI.fechacreacion,
                MO.descripcion
        ORDER BY RD.FECHA DESC";

// '20602601286'

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
        $documentos=DB::connection($sql_base)
                        ->select(DB::raw($query),[$request->idclieprov]);

        foreach ($documentos as $key => $documento) {
            $ca=CostoAsignado::where('idrecepcion',$documento->idrecepcion )
                                ->where('item', $documento->item)
                                ->where('empresa',$request->empresa)
                                ->first();
            if ($ca!=null) {
                $documento->con_ccosto= Carbon::parse($ca->created_at)->format('d/m/Y H:i');
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