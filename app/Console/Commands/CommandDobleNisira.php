<?php

namespace App\Console\Commands;

use App\Model\Evento;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Mail;
use Carbon\Carbon;

class CommandDobleNisira extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:doblenisira';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detecta doble Conexión de nisira';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $data=DB::connection('sqlsrv_proserla')
                    ->select(DB::raw("select	P.[user],
                                                count(id) cantidad, 
                                                STUFF(
                                                    (SELECT  db +', '
                                                    FROM PRIMARYKEYS
                                                    WHERE [user] = P.[user]
                                                    FOR XML PATH (''))
                                                , 1, 0, '') as evento  
                                    from PRIMARYKEYS as P
                                    WHERE inicio<DATEADD(minute, -5, GETDATE())
                                    GROUP BY P.[user]
                                    HAVING count(id)>1"));
        

        $subject = "Evento en Licencias Nisira";
        $for = "sistemas.proserla@gmail.com";
        $cc = "wheredia@proserla.com";

        foreach ($data as $key => $value) {
            $usuario=$value->user;
            $subject = "Evento en Licencias Nisira ".$usuario;

            $eventoEncontrado=Evento::where('tipo','doblenisira')
                                ->where('user_nisira',$usuario)
                                ->where('created_at','>',Carbon::now()->subHour())
                                ->first();
            if ($eventoEncontrado==null) {
                $tipo="doblenisira";
                $msjEvento="Más de un Nisira abierto, bases de datos=".$value->evento;
                $evento=new Evento();
                $evento->user_nisira=$value->user;
                $evento->descripcion=$msjEvento;
                $evento->tipo=$tipo;
                $evento->save();
                try {
                    Mail::send('mail',compact('msjEvento','usuario'), function($msj) use($subject,$for,$cc){
                        $msj->from("sistemas.proserla@gmail.com","Sistemas Proserla");
                        $msj->subject($subject);
                        $msj->to($for)->cc($cc);
                    });
                    echo "enviado <br>";
                } catch (\Exception $ex) {
                    
                }
            }
        }

    }
}
