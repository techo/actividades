<template>
    <div class="box">
        <div class="box-header with-border">
            <h2 class="box-title"><strong>Evaluaciones entre inscriptos</strong></h2>
            <span class="pull-right">
                <a  class="btn btn-primary" :href="urlExportar">
                    <i class="fa fa-download"></i>
                    Descargar Excel
                </a>
            </span>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-6 divisor">
                    <evaluaciones-voluntarios-stats></evaluaciones-voluntarios-stats>
                </div>
                <div class="col-md-6">
                    <evaluaciones-voluntarios-chart></evaluaciones-voluntarios-chart>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div v-show="loading" class="overlay">
            <i class="fa fa-refresh fa-spin"></i>
        </div>
    </div>
</template>

<script>
    import store from '../stores/store';
    export default {
        name: "evaluaciones-voluntarios",
        data(){
            return {
                chartLoading: true,
                statsLoading: true,
                urlExportar: "/admin/actividades/" + store.state.idActividad + "/exportar-evaluaciones-voluntarios",
            }
        },
        computed: {
            loading: function () {
                return this.chartLoading || this.statsLoading;
            }
        },
        created(){
            Event.$on('chart-voluntarios-loaded', this.chartLoaded);
            Event.$on('stats-voluntarios-loaded', this.statsLoaded);
        },
        methods: {
            chartLoaded: function () {
                this.chartLoading = false;
            },
            statsLoaded: function () {
                this.statsLoading = false;
            }
        }
    }
</script>

<style scoped>
    .divisor {
        border-right: 1px rgb(244,244,244) solid;
    }
</style>