<?php

namespace Tests\Unit;

use App\Services\Preguntas\ConditionEvaluator;
use PHPUnit\Framework\TestCase;

/**
 * Tests puros (sin BD) del evaluador de condiciones.
 * El evaluador acepta arrays asociativos, por eso no requiere modelos ni base.
 */
class ConditionEvaluatorTest extends TestCase
{
    private function escenario()
    {
        // "Elige tu zona" con 3 opciones + 2 dependientes (semáforo Norte/Sur).
        return [
            [
                'id' => 1,
                'tipo' => 'desplegable',
                'condiciones' => [],
                'opciones_detalle' => [
                    ['id' => 'opt_n', 'label' => 'Zona Norte'],
                    ['id' => 'opt_s', 'label' => 'Zona Sur'],
                    ['id' => 'opt_c', 'label' => 'Zona Centro'],
                ],
            ],
            [
                'id' => 2,
                'tipo' => 'desplegable',
                'opciones_detalle' => [],
                'condiciones' => [
                    ['parent_id' => 1, 'operator' => 'equals', 'value' => 'opt_n'],
                ],
            ],
            [
                'id' => 3,
                'tipo' => 'desplegable',
                'opciones_detalle' => [],
                'condiciones' => [
                    ['parent_id' => 1, 'operator' => 'equals', 'value' => 'opt_s'],
                ],
            ],
        ];
    }

    public function testSinRespuestaSoloPadreVisible()
    {
        $vis = ConditionEvaluator::visibleIds($this->escenario(), []);
        sort($vis);
        $this->assertEquals([1], $vis);
    }

    public function testZonaNorteMuestraDependienteNorte()
    {
        $vis = ConditionEvaluator::visibleIds($this->escenario(), [1 => 'Zona Norte']);
        sort($vis);
        $this->assertEquals([1, 2], $vis);
    }

    public function testZonaSurMuestraDependienteSur()
    {
        $vis = ConditionEvaluator::visibleIds($this->escenario(), [1 => 'Zona Sur']);
        sort($vis);
        $this->assertEquals([1, 3], $vis);
    }

    public function testZonaCentroNoMuestraDependientes()
    {
        $vis = ConditionEvaluator::visibleIds($this->escenario(), [1 => 'Zona Centro']);
        sort($vis);
        $this->assertEquals([1], $vis);
    }

    /**
     * Robustez: renombrar el label de la opción no rompe la condición, porque
     * se compara por id estable. Una respuesta NUEVA con el label nuevo sigue
     * activando la dependiente.
     */
    public function testRenombrarLabelNoRompeCondicion()
    {
        $preguntas = $this->escenario();
        $preguntas[0]['opciones_detalle'][0]['label'] = 'Norte'; // antes "Zona Norte"

        $vis = ConditionEvaluator::visibleIds($preguntas, [1 => 'Norte']);
        sort($vis);
        $this->assertEquals([1, 2], $vis);
    }

    /**
     * Cascada: si el padre está oculto, la dependiente también.
     */
    public function testCascadaPadreOculto()
    {
        $preguntas = $this->escenario();
        // 4 depende de 2; 2 depende de 1=opt_n.
        $preguntas[] = [
            'id' => 4,
            'tipo' => 'abierta',
            'opciones_detalle' => [],
            'condiciones' => [
                ['parent_id' => 2, 'operator' => 'equals', 'value' => 'cualquiera'],
            ],
        ];

        // Zona Sur → 2 oculta → 4 oculta.
        $vis = ConditionEvaluator::visibleIds($preguntas, [1 => 'Zona Sur']);
        sort($vis);
        $this->assertEquals([1, 3], $vis);
    }

    public function testPadreInexistenteFailOpen()
    {
        $preguntas = [
            [
                'id' => 9,
                'tipo' => 'abierta',
                'opciones_detalle' => [],
                'condiciones' => [
                    ['parent_id' => 999, 'operator' => 'equals', 'value' => 'x'],
                ],
            ],
        ];

        $vis = ConditionEvaluator::visibleIds($preguntas, []);
        $this->assertEquals([9], $vis);
    }
}
