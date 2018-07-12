<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <h2>Personas que evaluaron</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="row vertical-align">
                    <div class="col-md-3">
                        <h2><strong>{{ evaluaron }}</strong></h2>
                    </div>
                    <div class="col-md-9">
                        <h4 class="mt-0">Evaluaron</h4>
                    </div>
                </div>
                <div class="row vertical-align">
                    <div class="col-md-3">
                        <h2><strong>{{ pendientesEvaluar }}</strong></h2>
                    </div>
                    <div class="col-md-9">
                        <h4 class="mt-0">Faltan evaluar</h4>
                    </div>
                </div>
                <div class="row vertical-align">
                    <div class="col-md-3">
                        <h2><strong>{{ totalPresentes }}</strong></h2>
                    </div>
                    <div class="col-md-9">
                        <h4 class="mt-0">Total de presentes</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <!--grafico knob-->
                    <knob :valor="porcentajeEvaluaciones" :simbolo="'%'"></knob>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import knob from '../../plugins/knob';
    export default {
        name: "evaluaciones-actividad-stats",
        props: ['statsActividad'],
        components: { knob },
        data(){
            return {
                totalPresentes: 102,
                pendientesEvaluar: 12,
                evaluaron: 90,
            }
        },
        computed: {
          porcentajeEvaluaciones: function () {
              return Math.round(this.evaluaron * 100 / this.totalPresentes);
          }
        },
        methods: {
            getStats: function () {
                let url = "";
                axiosGet(
                    url,
                    //success callback
                    function (data, self) {

                    }
                    //payload
                    //error callback
                );
            }
        }
    }
</script>

<style scoped>

</style>