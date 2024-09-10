<?php


namespace App\Search\filters\inscripciones;

use App\Search\filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PendienteConfirmacion implements Filter
{
    public static function apply(Builder $builder, $value)
    {
        $condicion['comparacion'] = $value['condicion'];
        $condicion['valor'] = convertirCondicion($value['condicion'], $value['valor']);

        if ($condicion['valor'] == 1 || $condicion['valor'] == '%1%') 
        {
            $builder->where(function (Builder $queryConfirma) {
                $queryConfirma->where(function (Builder $queryConfirmado) {
                    $queryConfirmado->where('Actividad.confirmacion', '1')
                      ->where('Inscripcion.confirma', '0');
                })
                ->orWhere('Actividad.confirmacion', '0');
            })
            ->where(function (Builder $queryPago) {
                $queryPago->where(function (Builder $queryPagado) {
                    $queryPagado->where('Actividad.pago', '1')
                      ->where('Inscripcion.pago', '0');
                })
                ->orWhere('Actividad.pago', '0');
            });
        }
        return $builder;
    }
}