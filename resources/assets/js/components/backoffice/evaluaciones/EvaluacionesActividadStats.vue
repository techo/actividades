<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <h4>Personas que evaluaron</h4>
            </div>
            <div class="col-md-6">
                <h4 class="text-center">Porcentaje de evaluaciones</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h1><strong>{{ evaluaron }}</strong></h1>
                        <h4>Evaluaron</h4>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h1><strong>{{ pendientesEvaluar }}</strong></h1>
                        <h4>Faltan evaluar</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Promedio evaluaciones</h4>
                        <h1 class="promedio"><strong>{{ promedio }}</strong></h1>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <!--grafico knob-->
                <knob :valor="porcentajeEvaluaciones"
                      :simbolo="'%'"
                      :listener="'knob-eval-actividad-upd'"
                ></knob>
            </div>
        </div>
    </div>
</template>

<script>
    import knob from '../../plugins/knob';
    import store from '../stores/store';

    export default {
        name: "evaluaciones-actividad-stats",
        components: { knob },
        data(){
            return {
                evaluaron: 0,
                promedio: 0,
                loading: true,
              //  presentes: store.state.presentes,
            }
        },
        computed: {
          porcentajeEvaluaciones: function () {
              if(this.loading) return 0;
              if(store.state.presentes === 0) return 0;

              let porcentaje = Math.round(this.evaluaron * 100 / store.state.presentes);
              Event.$emit("knob-eval-actividad-upd", porcentaje);
              return porcentaje;
          },
          pendientesEvaluar: function () {
              if(this.loading) return 0;
              return store.state.presentes - this.evaluaron;
            }
        },
        created(){
            this.getStats();
        },
        methods: {
            getStats: function () {
                let url = window.location.origin + "/admin/ajax/actividades/" + store.state.idActividad + "/evaluaciones/stats";
                this.axiosGet(
                    url,
                    //success callback
                    function (data, self) {
                        self.evaluaron = data.evaluaron;
                        self.promedio = data.promedio;
                        Event.$emit('stats-actividad-loaded');
                        self.loading = false;
                    }
                    //payload
                    //error callback
                );
            }
        }
    }
</script>

<style scoped>
    .promedio {
        font-size: 300%;
    }
</style>