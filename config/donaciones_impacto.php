<?php

/*
|--------------------------------------------------------------------------
| Constantes del dashboard de impacto de donaciones
|--------------------------------------------------------------------------
|
| Alimenta las tres tarjetas del dashboard de impacto (ver DonationImpactService
| y api/DonationController@impact).
|
| DECISIONES DE PRODUCTO (reunión 09-2026):
|   1. La moneda mostrada es SIEMPRE la moneda local del donante. No hay
|      conversión a USD. Por eso los costos de referencia se definen por moneda.
|   2. Los promedios de impacto de la organización y los costos son valores
|      DUMMY (placeholder). El dueño del dato (datos/finanzas) los reemplaza
|      acá, sin tocar código.
|
| ⚠ TODOS los números marcados DUMMY son placeholders derivados del baseline
|   USD del documento original (203/m² · 3315/vivienda · 106/voluntario),
|   escalados por país sólo para que se vean plausibles. NO son costos reales.
|   Reemplazar por costos locales validados.
|
*/

return [

    // Metros cuadrados de una vivienda estándar (6x6 = 36 m²). La barra de
    // progreso de la tarjeta de impacto usa esto como meta (1 vivienda completa).
    //
    // ⚠ INVARIANTE: para que la barra (m²/meta) y el hito de "vivienda completa"
    //   (total/costo_vivienda) coincidan, debe valer costo_vivienda ≈ costo_m2 × 36.
    //   Si datos/finanzas define un costo de vivienda con costos fijos que no sea
    //   exactamente 36× el m², la barra y el hito mostrarán valores levemente
    //   distintos (a revisar en ese momento).
    'meta_m2_vivienda' => 36,

    // Moneda usada cuando el donante aportó en una moneda sin costos definidos
    // abajo. Debe existir en 'costos'.
    'moneda_fallback' => 'usd',

    /*
    |----------------------------------------------------------------------
    | Costos de referencia por moneda — en UNIDAD MAYOR de cada moneda
    |----------------------------------------------------------------------
    | m2         : costo promedio de construir 1 m².
    | vivienda   : costo promedio de 1 vivienda completa (36 m², 6x6).
    | voluntario : "unidad logística" — costo logístico promedio por voluntario
    |              movilizado (base del split de la tarjeta de logística).
    |
    | DUMMY. Reemplazar por los costos locales reales de cada país.
    | Los 'vivienda' dummy son m2 × 36 (ver INVARIANTE en meta_m2_vivienda).
    */
    'costos' => [
        'usd' => ['m2' => 203,     'vivienda' => 7308,     'voluntario' => 106],
        'ars' => ['m2' => 284000,  'vivienda' => 10224000, 'voluntario' => 148000],
        'bob' => ['m2' => 1421,    'vivienda' => 51156,    'voluntario' => 742],
        'brl' => ['m2' => 1015,    'vivienda' => 36540,    'voluntario' => 530],
        'clp' => ['m2' => 186800,  'vivienda' => 6724800,  'voluntario' => 97500],
        'cop' => ['m2' => 730800,  'vivienda' => 26308800, 'voluntario' => 381600],
        'crc' => ['m2' => 93380,   'vivienda' => 3361680,  'voluntario' => 48760],
        'dop' => ['m2' => 11774,   'vivienda' => 423864,   'voluntario' => 6148],
        'gtq' => ['m2' => 1624,    'vivienda' => 58464,    'voluntario' => 848],
        'hnl' => ['m2' => 5278,    'vivienda' => 190008,   'voluntario' => 2756],
        'mxn' => ['m2' => 3451,    'vivienda' => 124236,   'voluntario' => 1802],
        'pyg' => ['m2' => 1258600, 'vivienda' => 45309600, 'voluntario' => 657200],
        'pen' => ['m2' => 690,     'vivienda' => 24840,    'voluntario' => 360],
        'uyu' => ['m2' => 8120,    'vivienda' => 292320,   'voluntario' => 4240],
        'ves' => ['m2' => 117740,  'vivienda' => 4238640,  'voluntario' => 61480],
    ],

    // Monedas sin decimales (unidad mayor == unidad menor). El resto usa 2.
    // Coincide con la lista de zero-decimal currencies de Stripe.
    'monedas_sin_decimales' => ['clp', 'pyg'],

    /*
    |----------------------------------------------------------------------
    | Distribución del costo logístico (tarjeta C). Debe sumar 1.0
    |----------------------------------------------------------------------
    */
    'logistica_split' => [
        'fletes'       => 0.50, // Traslado de paneles, madera y materiales.
        'voluntariado' => 0.30, // Buses, alimentación y seguros de voluntarios.
        'herramientas' => 0.20, // Kits de trabajo (palas, cascos, guantes, clavos).
    ],

    /*
    |----------------------------------------------------------------------
    | Promedios mensuales de impacto de TODA la organización (Latinoamérica)
    |----------------------------------------------------------------------
    | Alimentan el "Reloj de impacto": se multiplican por los meses activos
    | del donante. DUMMY — el dueño del dato los reemplaza.
    */
    'promedios_org' => [
        'viviendas'   => 315,
        'voluntarios' => 9556,
        'mesas'       => 40,
    ],
];
