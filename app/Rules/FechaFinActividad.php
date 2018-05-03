<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class FechaFinActividad implements Rule
{
    protected $fechaInicio;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($fechaInicio)
    {
        $this->fechaInicio = $this->trimDate($fechaInicio);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $inicio = \Illuminate\Support\Carbon::parse($this->trimDate($this->fechaInicio));

        $fin = \Illuminate\Support\Carbon::parse($this->trimDate($value))
            ->addHours(23)
            ->addMinutes(59);

        return $fin->gte($inicio);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'La fecha de fin de la actividad debe ser posterior a la fecha de inicio';
    }

    private function trimDate($fecha)
    {
        $i = strpos($fecha, 'T');
        if ($i > 0) {
            return substr($fecha, 0, $i);
        }
        return $fecha;
    }

}
