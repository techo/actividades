<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <h4>Personas que evaluaron</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="row vertical-align">
                    <div class="col-md-3">
                        <h2 class="text-center"><strong>{{ evaluaron }}</strong></h2>
                    </div>
                    <div class="col-md-9">
                        <h4 class="mt-0">Evaluaron</h4>
                    </div>
                </div>
                <div class="row vertical-align">
                    <div class="col-md-3">
                        <h2 class="text-center"><strong>{{ pendientesEvaluar }}</strong></h2>
                    </div>
                    <div class="col-md-9">
                        <h4 class="mt-0">Faltan evaluar</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <!--grafico knob-->
                    <knob :valor="porcentajeEvaluaciones"
                          :simbolo="'%'"
                          :listener="'knob-eval-voluntarios-upd'"
                    ></knob>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import knob from '../../plugins/knob';
    import store from '../stores/store';

    export default {
        name: "evaluaciones-voluntarios-stats",
        components: { knob },
        data(){
            return {
                evaluaron: 0,
                loading: true,
              //  presentes: store.state.presentes,
            }
        },
        computed: {
          porcentajeEvaluaciones: function () {
              if(this.loading) return 0;

              let porcentaje = Math.round(this.evaluaron * 100 / store.state.presentes);
              Event.$emit("knob-eval-voluntarios-upd", porcentaje);
              return porcentaje;
          },
          totalPresentes: function () {
              return store.state.presentes;
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
                let url = window.location.origin + "/admin/ajax/actividades/" + store.state.idActividad + "/evaluaciones/voluntarios/stats";
                this.axiosGet(
                    url,
                    //success callback
                    function (data, self) {
                        // store.commit("updatePresentes", data.presentes);
                        self.evaluaron = data.evaluaron;
                        Event.$emit('stats-voluntarios-loaded');
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

</style>