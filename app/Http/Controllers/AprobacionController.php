<?php

namespace App\Http\Controllers;

use App\Model\NPrivilegiosAprobar;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AprobacionController extends Controller
{
    public function edt(Request $request){
        $aprobar=NPrivilegiosAprobar::select(DB::raw('RTRIM(IDUSUARIO) IDUSUARIO,RTRIM(PRIVILEGIOS_APROBAR.FORMULARIO) FORMULARIO,SUBSTRING(TABLA, 4, LEN ( TABLA )-1) TABLA,KEYASOCIADO PRIMARYKEY,FORMULARIOS.DESCRIPCION,APRUEBA'))
                ->join('FORMULARIOS','FORMULARIOS.FORMULARIO','=','PRIVILEGIOS_APROBAR.FORMULARIO')
                ->where('APRUEBA','1')
                ->where('IDUSUARIO',$request->usuario)
                ->get();
        return response()->json($aprobar);
    }

    public function pendientes(Request $request){
        $tabla=$request->tabla;
        /**
         * Busca si es IDCLIENTEPROV o ID RESPONSABLE
         */
        $existe_clienpro=DB::connection('sqlsrv')
                            ->select(DB::raw("SELECT COUNT(COLUMN_NAME) cantidad FROM Information_Schema.Columns WHERE TABLE_NAME=? AND COLUMN_NAME=?"),[
                                $tabla,'IDCLIEPROV'
                            ]);
        $existe_idresponsable=DB::connection('sqlsrv')
                            ->select(DB::raw("SELECT COUNT(COLUMN_NAME) cantidad FROM Information_Schema.Columns WHERE TABLE_NAME=? AND COLUMN_NAME=?"),[
                                $tabla,'IDRESPONSABLE'
                            ]);
        //estado anterior                    
        $estado=DB::connection('sqlsrv')
                            ->select(DB::raw("SELECT TOP 1 idestado 
                            from SECUENCIA_FLUJO 
                                INNER JOIN FORMULARIOS ON FORMULARIOS.FORMULARIO=SECUENCIA_FLUJO.FORMULARIO_ORIGEN
                            where SUBSTRING(TABLA, 4, LEN ( TABLA )-1) = ?  
                                    AND FORMULARIO_ORIGEN = FORMULARIO_DESTINO 
                                    AND IDESTADO <> 'AP'
                            order by SECUENCIA desc"),[$tabla]);
        if ($tabla=='PEDIDO') {
            $pendientes=DB::connection('sqlsrv')
                            ->select(DB::raw("  SELECT T.*, isnull( C.NOMBRE, 'No Asignado') AS destinatariodoc 
                                                FROM $tabla  AS T 
                                                left JOIN RESPONSABLE AS C ON T.idresponsable = c.IDRESPONSABLE
                                                WHERE T.idestado = ?"),[$estado[0]->idestado]);
        }elseif ($existe_clienpro[0]->cantidad&&$tabla!="REQINTERNO") {
            $pendientes=DB::connection('sqlsrv')
                            ->select(DB::raw("  SELECT T.*, isnull( C.RAZON_SOCIAL, 'No Asignado') AS destinatariodoc 
                                                FROM $tabla  AS T 
                                                left JOIN CLIEPROV AS C ON T.idclieprov = C.IDCLIEPROV
                                                WHERE T.idestado = ?"),[$estado[0]->idestado]);
            // dd("hola");
        }elseif ($existe_idresponsable[0]->cantidad) {
            if ($tabla=="REQINTERNO") {
                $pendientes=DB::connection('sqlsrv')
                                ->select(DB::raw("  SELECT T.*, M.DESCRIPCION motivo, isnull( C.NOMBRE, 'No Asignado') AS destinatariodoc 
                                                    FROM $tabla  AS T 
                                                    left JOIN RESPONSABLE AS C ON T.idresponsable = c.IDRESPONSABLE
                                                    LEFT JOIN MOTIVOSREQINTERNO AS M ON M.IDMOTIVO=T.IDMOTIVO
                                                    WHERE T.idestado = ?"),[$estado[0]->idestado]);
            }else{
                $pendientes=DB::connection('sqlsrv')
                                ->select(DB::raw("  SELECT T.*, isnull( C.NOMBRE, 'No Asignado') AS destinatariodoc 
                                                    FROM $tabla  AS T 
                                                    left JOIN RESPONSABLE AS C ON T.idresponsable = c.IDRESPONSABLE
                                                    WHERE T.idestado = ?"),[$estado[0]->idestado]);
            }
        }else{
            $pendientes=DB::connection('sqlsrv')
                            ->select(DB::raw("  SELECT T.*, 'No Asignado' AS destinatariodoc 
                                                FROM $tabla  AS T 
                                                WHERE T.idestado = ?"),[$estado[0]->idestado]);
        }
        return response()->json($this->keyMin($pendientes),200);
    }
    public function detalles(Request $request){
        
        $tabla=$request->tabla;
        $primarykey=$request->primarykey;
        $id=$request->id;

        $existe_clienpro=DB::connection('sqlsrv')
                            ->select(DB::raw("SELECT COUNT(COLUMN_NAME) cantidad FROM Information_Schema.Columns WHERE TABLE_NAME=? AND COLUMN_NAME=?"),[
                                $tabla,'IDCLIEPROV'
                            ]);
        $existe_idresponsable=DB::connection('sqlsrv')
                            ->select(DB::raw("SELECT COUNT(COLUMN_NAME) cantidad FROM Information_Schema.Columns WHERE TABLE_NAME=? AND COLUMN_NAME=?"),[
                                $tabla,'IDRESPONSABLE'
                            ]);
                
        if ($existe_clienpro[0]->cantidad) {
            $pendientes=DB::connection('sqlsrv')
            ->select(DB::raw("  SELECT T.*, isnull( C.RAZON_SOCIAL, 'No Asignado') AS destinatariodoc 
                                        FROM $tabla  AS T 
                                        left JOIN CLIEPROV AS C ON T.idclieprov = C.IDCLIEPROV
                                        WHERE T.$primarykey = '$id'"));
        }elseif ($existe_idresponsable[0]->cantidad) {
            $pendientes=DB::connection('sqlsrv')
            ->select(DB::raw("  SELECT T.*, isnull( C.NOMBRE, 'No Asignado') AS destinatariodoc 
                                                FROM $tabla  AS T 
                                                left JOIN RESPONSABLE AS C ON T.idresponsable = c.IDRESPONSABLE
                                                WHERE T.$primarykey = '$id'"));
        }else{
            $pendientes=DB::connection('sqlsrv')
            ->select(DB::raw("  SELECT T.*, 'No Asignado' AS destinatariodoc 
                                                FROM $tabla  AS T 
                                                WHERE T.$primarykey = '$id'"));
        }
        // $pendientes
        $detalles=DB::connection('sqlsrv')
                    ->select(DB::raw("  SELECT  *  FROM d$tabla WHERE  $primarykey = '$id'"));
        // $detalles=collect($detalles)->map(function($x){ return array_change_key_case((array)$x,CASE_LOWER); })->toArray();
        // dd(array_change_key_case($detalles,CASE_LOWER));
        return response()->json([
            "documento" => array_change_key_case((array)$pendientes[0],CASE_LOWER),
            "detalles"  => $this->keyMin($detalles)
        ]);
    }

    public function aprobar(Request $request){
        // return response()->json([
        //     "status"    =>  "OK",
        // ]);
        $tabla=$request->tabla;
        $primarykey=$request->primarykey;
        $id=$request->id;
        $usuario=$request->usuario;
        $formulario=$request->formulario;
        
        $updateData=[];
        if ( $tabla == 'REQINTERNO' ) {
            $updateData=[
                "idestado"=>"AP"
            ];
        }else {
            $updateData=[
                "idestado"=>"AP",
                "nrsidusuario_ap"=>$usuario,
                "nsrfecha_ap"=>Carbon::now(),
            ];
        }

        $operacion=DB::connection('sqlsrv')
                            ->table($tabla)
                            ->where($primarykey,$id)
                            ->update($updateData);
                            // ->select(DB::raw("UPDATE $tabla set idestado = 'AP' where  $primarykey= '$id'"),[]);
        $operacion2=DB::connection('sqlsrv')
                            ->table('LOGESTADOS')
                            ->insert([
                                "idempresa" =>"001",
                                "idcodigo"  =>$id,
                                "idusuario" =>$usuario,
                                "archivo"   =>$tabla,
                                "formulario"=>$formulario,
                                "idestado"  =>"AP",
                                "fecha"     => Carbon::now(),
                                "fechacreacion"=> Carbon::now(),
                                "sincroniza"=> "N",
                                "de"=> null,
                                "maquina"=> "MOVIL\\$usuario",
                            ]);        
        return response()->json([
            "status"    =>  "OK",
        ],200);
    }

    public function login(Request $request){
        $usuario=$request->usuario;
        $password=$request->password;
        // dd($request->all());
        $logeo=DB::connection('sqlsrv')
                ->select(DB::raw("select IDUSUARIO from USUARIO where IDUSUARIO=? AND PASSWORD=?"),[$usuario,$password]);
        if (count($logeo)>0) {
            return response()->json([
                "status"    =>  "OK",
                "data"      =>  $logeo[0]
            ]);
        }else{
            return response()->json([
                "status"    =>  "NO",
                "data"      =>  "Usuario o contraseña incorrectos."
            ]);
        }
    }
    public function stock(Request $request){
        $usuario=$request->usuario;
        $idproducto=$request->idproducto;
        try {
            $query="select idproducto, SUM(CANTIDAD*FACTOR) STOCK, M.IDSUCURSAL , M.IDALMACEN, A.DESCRIPCION ALMACEN
            FROM MOVALMACEN M
            INNER JOIN ALMACENES A ON A.IDALMACEN=M.IDALMACEN 
            where IDPRODUCTO = ?
            GROUP BY M.IDPRODUCTO,M.IDSUCURSAL , M.IDALMACEN,A.DESCRIPCION";
            $data=DB::connection('sqlsrv')
                    ->select($query,[$idproducto]);
            return response()->json($this->keyMin($data));
        } catch (\Exception $th) {
        }
        // dd($request->all());
    }
    public function keyMin($array){
        return collect($array)->map(function($x){ return array_change_key_case((array)$x,CASE_LOWER); })->toArray();
    }
}
