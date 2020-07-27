<?php

namespace App\Console\Commands;

// use App\Model\NPrimaryKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

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
                                    GROUP BY P.[user]
                                    HAVING count(id)>1"));
        
        foreach ($data as $key => $value) {
            var_dump($value->user);
        }

    }
}
