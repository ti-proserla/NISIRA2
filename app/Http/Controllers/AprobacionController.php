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
        
        $existe_clienpro=DB::connection('sqlsrv')
                            ->select(DB::raw("SELECT COUNT(COLUMN_NAME) cantidad FROM Information_Schema.Columns WHERE TABLE_NAME=? AND COLUMN_NAME=?"),[
                                $tabla,'IDCLIEPROV'
                            ]);
        $existe_idresponsable=DB::connection('sqlsrv')
                            ->select(DB::raw("SELECT COUNT(COLUMN_NAME) cantidad FROM Information_Schema.Columns WHERE TABLE_NAME=? AND COLUMN_NAME=?"),[
                                $tabla,'IDRESPONSABLE'
                            ]);
        $estado=DB::connection('sqlsrv')
                            ->select(DB::raw("SELECT TOP 1 idestado 
                            from SECUENCIA_FLUJO 
                                INNER JOIN FORMULARIOS ON FORMULARIOS.FORMULARIO=SECUENCIA_FLUJO.FORMULARIO_ORIGEN
                            where SUBSTRING(TABLA, 4, LEN ( TABLA )-1) = ?  
                                    AND FORMULARIO_ORIGEN = FORMULARIO_DESTINO 
                                    AND IDESTADO <> 'AP'
                            order by SECUENCIA desc"),[$tabla]);

        
        if ($existe_clienpro[0]->cantidad) {
            $pendientes=DB::connection('sqlsrv')
                            ->select(DB::raw("  SELECT T.*, isnull( C.RAZON_SOCIAL, 'No Asignado') AS destinatariodoc 
                                                FROM $tabla  AS T 
                                                left JOIN CLIEPROV AS C ON T.idclieprov = C.IDCLIEPROV
                                                WHERE T.idestado = ?"),[$estado[0]->idestado]);
        }elseif ($existe_idresponsable[0]->cantidad) {
            $pendientes=DB::connection('sqlsrv')
                            ->select(DB::raw("  SELECT T.*, isnull( C.NOMBRE, 'No Asignado') AS destinatariodoc 
                                                FROM $tabla  AS T 
                                                left JOIN RESPONSABLE AS C ON T.idresponsable = c.IDRESPONSABLE
                                                WHERE T.idestado = ?"),[$estado[0]->idestado]);
        }else{
            $pendientes=DB::connection('sqlsrv')
                            ->select(DB::raw("  SELECT T.*, 'No Asignado' AS destinatariodoc 
                                                FROM $tabla  AS T 
                                                WHERE T.idestado = ?"),[$estado[0]->idestado]);
        }
        return response()->json($pendientes);
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
        $tabla=$request->tabla;
        $primarykey=$request->primarykey;
        $id=$request->id;
        $usuario=$request->usuario;
        $formulario=$request->formulario;
        $operacion=DB::connection('sqlsrv')
                            ->table($tabla)
                            ->where($primarykey,$id)
                            ->update([
                                "idestado"=>"AP",
                                "nrsidusuario_ap"=>$usuario,
                                "nsrfecha_ap"=>Carbon::now(),
                            ]);
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
            "data"      =>  $operacion,
            "data2"      =>  $operacion2
        ]);
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
            $query="DECLARE @table_into  TABLE(
                idsucursal varchar(3),
                sucursal varchar(20),
                idalmacen varchar(3),
                almacen varchar(20),
                idproducto varchar(20),
                producto varchar(200),
                idmedida varchar(5),
                idmedida2 varchar(5),
                idlotep varchar(5),
                idserie varchar(5),
                idubicacion varchar(5),
                idestadoproducto integer,
                estado varchar(10),
                pidelote integer,
                pideserie integer,
                es_aprovechable integer,
                peso numeric(17,4),
                peso_tara numeric(17,2),
                exige_u2 numeric(17,2),
                stock numeric(17,2),
                stock2 numeric(17,2),
                idproducto_ref varchar(15)
            )
            BEGIN
                INSERT @table_into exec NSP_RETURN_SALDOS_PRODUCTOS '001','002','005','20200713','','<?xml version=\"1.0\" encoding=\"Windows-1252\" standalone=\"yes\"?>
                        <VFPData>
                            <productos_buscar>
                                <idproducto>$idproducto</idproducto>
                            </productos_buscar>
                        </VFPData>','ADMINISTRADOR'
                SELECT * FROM @table_into
            END;";
            $data=DB::select($query,['001']);
            // dd("hola");
            // dd($data);
            return response()->json($this->keyMin($data));
        } catch (\Exception $th) {
            dd($th->getMessage());
        }
        // dd($request->all());
    }
    public function keyMin($array){
        return collect($array)->map(function($x){ return array_change_key_case((array)$x,CASE_LOWER); })->toArray();
    }
}
