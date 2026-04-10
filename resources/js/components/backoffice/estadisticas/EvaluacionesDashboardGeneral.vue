<template>
    <div>
        <!-- Stat cards -->
        <evaluaciones-general-stats :filtros="filtros"></evaluaciones-general-stats>

        <!-- Evaluaciones de actividad: promedio, NPS, histograma, comentarios -->
        <evaluaciones-actividad :filtros="filtros"></evaluaciones-actividad>

        <!-- Tags: atributos y puntos de mejora -->
        <evaluaciones-tags-resumen :filtros="filtros"></evaluaciones-tags-resumen>

        <!-- Competencias + Impacto -->
        <div class="row">
            <div class="col-md-7">
                <evaluaciones-competencias :filtros="filtros"></evaluaciones-competencias>
            </div>
            <div class="col-md-5">
                <evaluaciones-impacto :filtros="filtros"></evaluaciones-impacto>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'evaluaciones-dashboard-general',
        props: ['filtros'],
    }
</script>
