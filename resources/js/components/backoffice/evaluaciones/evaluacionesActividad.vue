<template>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><strong>{{ $t('backend.activity_evaluations') }}</strong></h3>
            <span class="pull-right">
                <a class="btn btn-primary btn-sm" :href="urlExportar">
                    <i class="fa fa-download"></i>
                    {{ $t('backend.download_excel') }}
                </a>
            </span>
        </div>
        <div class="box-body">
            <!-- Fila 1: promedio + NPS (izquierda) | estado circular (derecha) -->
            <evaluaciones-actividad-stats :id="id" :filtros="filtros"></evaluaciones-actividad-stats>
            <!-- Fila 3: comentarios -->
            <evaluaciones-comentarios :id="id" :filtros="filtros"></evaluaciones-comentarios>
        </div>
    </div>
</template>

<script>
    import EvaluacionesActividadStats from './EvaluacionesActividadStats';
    import EvaluacionesActividadChart from './EvaluacionesActividadChart';
    import EvaluacionesComentarios    from './EvaluacionesComentarios';

    export default {
        name: "evaluaciones-actividad",
        props: ['id', 'filtros'],
        components: {
            EvaluacionesActividadStats,
            EvaluacionesActividadChart,
            EvaluacionesComentarios,
        },
        computed: {
            urlExportar() {
                if (this.id) return '/admin/actividades/' + this.id + '/exportar-evaluaciones';
                var qs = '';
                if (this.filtros) {
                    var parts = [];
                    if (this.filtros.año)     parts.push('año='     + this.filtros.año);
                    if (this.filtros.pais)    parts.push('pais='    + this.filtros.pais);
                    if (this.filtros.oficina) parts.push('oficina=' + this.filtros.oficina);
                    if (parts.length) qs = '?' + parts.join('&');
                }
                return '/admin/ajax/estadisticas/evaluaciones/exportar-actividad' + qs;
            }
        }
    }
</script>
