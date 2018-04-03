<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class dbfix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'techo:dbfix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        ini_set('max_execution_time', 30000);
        $actividades = \App\Actividad::all();
        foreach ($actividades as $actividad) {
            $date = \Carbon\Carbon::create(2017, 5, 28, 0, 0, 0);
            try {
                if (\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $actividad->fechaInicio) !== false) {
                    $actividad->fechaInicioInscripciones =  $date->addWeeks(rand(1, 50))->format('Y-m-d H:i:s');
                    $actividad->fechaFinInscripciones =  $actividad->fechaInicioInscripciones->addDays(rand(1, 5))->format('Y-m-d H:i:s');
                    $actividad->fechaInicio =  $actividad->fechaFinInscripciones->addWeeks(rand(1, 5))->format('Y-m-d H:i:s');
                    $actividad->fechaFin =  $actividad->fechaInicio->addDays(rand(1, 12))->format('Y-m-d H:i:s');
                    $actividad->fechaCreacion= $actividad->fechaInicioInscripciones;
                    $actividad->fechaModificacion= $actividad->fechaInicioInscripciones;
                    $actividad->fechaInicioEvaluaciones = $actividad->fechaFin->addDays(1)->format('Y-m-d H:i:s');;
                    $actividad->fechaFinEvaluaciones = $actividad->fechaFin->addDays(5)->format('Y-m-d H:i:s');;
                    $actividad->save();
                }

            } catch (\Exception $exception) {
                $actividad->fechaInicioInscripciones =  $date->addWeeks(rand(1, 50))->format('Y-m-d H:i:s');
                $actividad->fechaFinInscripciones =  $actividad->fechaInicioInscripciones->addDays(rand(1, 5))->format('Y-m-d H:i:s');
                $actividad->fechaInicio =  $actividad->fechaFinInscripciones->addWeeks(rand(1, 5))->format('Y-m-d H:i:s');
                $actividad->fechaFin =  $actividad->fechaInicio->addDays(rand(1, 12))->format('Y-m-d H:i:s');
                $actividad->fechaCreacion= $actividad->fechaInicioInscripciones;
                $actividad->fechaModificacion= $actividad->fechaInicioInscripciones;
                $actividad->fechaInicioEvaluaciones = $actividad->fechaFin->addDays(1)->format('Y-m-d H:i:s');;
                $actividad->fechaFinEvaluaciones = $actividad->fechaFin->addDays(5)->format('Y-m-d H:i:s');;
                $actividad->save();
            }
//        break;
        }
    }
}
