<?php

namespace Tests\Unit;

use App\Actividad;
use App\Inscripcion;
use App\Services\EstadoInscripcion;
use Tests\TestCase;

/**
 * EstadoInscripcion con exención de pago por socio.
 *
 * `exento_pago` debe satisfacer el requisito de pago igual que un pago efectivo:
 * un socio no queda "esperando pago". La exención saca SOLO el pago, no la
 * confirmación del coordinador. No usa base de datos (modelos en memoria).
 */
class EstadoInscripcionExentoTest extends TestCase
{
    private function actividad($pago, $confirmacion): Actividad
    {
        $a = new Actividad();
        $a->pago         = $pago;
        $a->confirmacion = $confirmacion;
        return $a;
    }

    private function inscripcion(array $attrs): Inscripcion
    {
        $i = new Inscripcion();
        $i->pago        = $attrs['pago'] ?? 0;
        $i->confirma    = $attrs['confirma'] ?? 0;
        $i->exento_pago = $attrs['exento_pago'] ?? 0;
        return $i;
    }

    /** @test */
    public function actividad_paga_sin_confirmacion_con_exencion_queda_confirmada()
    {
        $estado = EstadoInscripcion::resolve(
            $this->actividad(1, 0),
            $this->inscripcion(['pago' => 0, 'exento_pago' => 1])
        );

        $this->assertEquals(EstadoInscripcion::CONFIRMED, $estado);
    }

    /** @test */
    public function actividad_paga_con_confirmacion_pendiente_y_exencion_sigue_esperando_confirmacion()
    {
        // La exención saca el pago, pero la aprobación del coordinador sigue pendiente.
        $estado = EstadoInscripcion::resolve(
            $this->actividad(1, 1),
            $this->inscripcion(['pago' => 0, 'confirma' => 0, 'exento_pago' => 1])
        );

        $this->assertEquals(EstadoInscripcion::WAITING_CONFIRMATION, $estado);
    }

    /** @test */
    public function actividad_paga_con_confirmacion_ok_y_exencion_queda_confirmada()
    {
        $estado = EstadoInscripcion::resolve(
            $this->actividad(1, 1),
            $this->inscripcion(['pago' => 0, 'confirma' => 1, 'exento_pago' => 1])
        );

        $this->assertEquals(EstadoInscripcion::CONFIRMED, $estado);
    }

    /** @test */
    public function sin_exencion_una_actividad_paga_impaga_sigue_pidiendo_pago()
    {
        // Control: sin exención el comportamiento previo no cambia.
        $estado = EstadoInscripcion::resolve(
            $this->actividad(1, 0),
            $this->inscripcion(['pago' => 0, 'exento_pago' => 0])
        );

        $this->assertEquals(EstadoInscripcion::CONFIRM_BY_PAYING, $estado);
    }
}
