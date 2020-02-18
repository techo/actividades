<template>
    <div class="box">
        <div class="box-header with-border">
            <h2 class="box-title"><strong>Evaluaciones de la actividad</strong></h2>
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
                    <evaluaciones-actividad-stats :id="id" ></evaluaciones-actividad-stats>
                </div>
                <div class="col-md-6">
                    <evaluaciones-actividad-chart :id="id" ></evaluaciones-actividad-chart>
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
    export default {
        name: "evaluaciones-actividad",
        props: [ 'id' ],
        data(){
            return {
                chartLoading: true,
                statsLoading: true,
                urlExportar: '',
                idActividad: null,
            }
        },
        computed: {
            loading: function () {
                return this.chartLoading || this.statsLoading;
            }
        },
        created(){
            this.urlExportar = "/admin/actividades/" + this.id + "/exportar-evaluaciones";
            this.idActividad = this.id;

            Event.$on('chart-actividad-loaded', this.chartLoaded);
            Event.$on('stats-actividad-loaded', this.statsLoaded);
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