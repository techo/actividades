<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Actividad;
use Mail;
#use App\Mail\CancelacionActividad;
use App\Jobs\EnviarMailsCancelacionActividad;


class EnviarCancelacionActividad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actividad:cancelacion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia cancelacions a los usuario inscriptos en una actividad';

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

        $actividad = Actividad::find(10575);
        foreach ($actividad->inscripciones as $inscripcion) {
            $job = (new EnviarMailsCancelacionActividad($inscripcion));
            dispatch($job);
        };
    }
}
