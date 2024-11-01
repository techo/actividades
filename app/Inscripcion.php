<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\SoftDeletes;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Inscripcion extends Model
{
    use SoftDeletes;

    protected $table = 'Inscripcion';
    protected $primaryKey = 'idInscripcion';
    protected $dates = ['fechaInscripcion', 'deleted_at'];
    protected $guarded = ['idInscripcion'];
    protected $casts = [
        'roles_aplicados' => 'array',
        'inscripciones_aplicadas' => 'array',
    ];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class, 'idActividad', 'idActividad');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'idPersona', 'idPersona');
    }

    public function punto_encuentro()
    {
        return $this->belongsTo(PuntoEncuentro::class, 'idPuntoEncuentro', 'idPuntoEncuentro');
    }


    protected static function boot()
    {
        parent::boot();

        self::saving(function($inscripcion){
            if($usuario = Auth::user()) {
                $inscripcion->idPersonaModificacion = $usuario->idPersona;
            }

        });

        static::deleting(function ($inscripcion) {
            //borrar registros de grupos
            GrupoRolPersona::where('idPersona', '=', $inscripcion->persona->idPersona)
                ->where('idActividad', '=', $inscripcion->actividad->idActividad)
                ->delete();
        });

        static::updating(function ($inscripcion) { Auditoria::crear($inscripcion); });

    }

    public function scopePresente($query)
    {
        return $query->where('presente', '=', '1' );
    }

    public function scopeAusente($query)
    {
        return $query->where('presente', '=', '0' );
    }

    public function jornadas()
    {
        return $this->belongsToMany(Jornada::class, 'InscripcionJornada', 'idInscripcion', 'idJornada');
    }

    public function QRCode()
    {
        $url = env("APP_URL").'/admin/actividades/'.$this->actividad->idActividad.'/inscripcion/'.$this->idInscripcion.'/persona/'.$this->persona->idPersona; 
        $qrCode = QrCode::size(200)->generate($url);

        return $qrCode;
    }

}
