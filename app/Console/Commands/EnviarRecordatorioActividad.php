<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Actividad;
use Mail;
use App\Mail\RecordatorioActividad;


class EnviarRecordatorioActividad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actividad:recordatorio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia recordatorios a los usuario inscriptos en una actividad';

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
     * @return mixed
     */
    public function handle()
    {
        $hoy = new Carbon();
        $hoy->subMinute();
        $manana = $hoy->copy()->addDays(1);  
        $actividades = Actividad::whereBetween('fechaInicio',[$hoy, $manana])->get();
        foreach ($actividades as $actividad) {
            foreach ($actividad->inscripciones_validas() as $inscripcion) {
                Mail::to($inscripcion->persona->mail)->send(new RecordatorioActividad($inscripcion->persona, $actividad));

                echo($inscripcion->persona->mail);
            }
        }
    }
}
