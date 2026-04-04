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
            <!-- Fila superior: stats + histograma -->
            <div class="row">
                <div class="col-md-8">
                    <evaluaciones-actividad-stats :id="id"></evaluaciones-actividad-stats>
                </div>
            </div>
            <!-- Fila 3: comentarios -->
            <evaluaciones-comentarios :id="id"></evaluaciones-comentarios>
        </div>
    </div>
</template>

<script>
    import EvaluacionesActividadStats from './EvaluacionesActividadStats';
    import EvaluacionesActividadChart from './EvaluacionesActividadChart';
    import EvaluacionesComentarios    from './EvaluacionesComentarios';

    export default {
        name: "evaluaciones-actividad",
        props: ['id'],
        components: {
            EvaluacionesActividadStats,
            EvaluacionesActividadChart,
            EvaluacionesComentarios,
        },
        computed: {
            urlExportar() {
                return "/admin/actividades/" + this.id + "/exportar-evaluaciones";
            }
        }
    }
</script>
