<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\CancelacionActividad;
use Mail;

class EnviarMailsCancelacionActividad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $actividad;
    public $persona;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($actividad, $persona)
    {
        $this->actividad = $actividad;
        $this->persona = $persona;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->persona->mail)->send(new CancelacionActividad($this->persona, $this->actividad));
        //sleep(3);
    }
}
