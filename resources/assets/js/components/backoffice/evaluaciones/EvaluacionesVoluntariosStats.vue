<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <h4>Personas que evaluaron</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h1><strong>{{ evaluaron }}</strong></h1>
                        <h4 class="mt-0">Evaluaron</h4>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h1><strong>{{ pendientesEvaluar }}</strong></h1>
                        <h4 class="mt-0">Faltan evaluar</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Promedio puntajes</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h1 class="promedio"><strong>{{ promedioSocial }}</strong></h1>
                        <h5>Social</h5>
                    </div>
                    <div class="col-md-6">
                        <h1 class="promedio"><strong>{{ promedioTecnico }}</strong></h1>
                        <h5>Técnico</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!--grafico knob-->
                <knob :valor="porcentajeEvaluaciones"
                      :simbolo="'%'"
                      :listener="'knob-eval-voluntarios-upd'"
                ></knob>
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
                promedioSocial: 0,
                promedioTecnico: 0,
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
                        self.promedioSocial = data.promedioSocial;
                        self.promedioTecnico = data.promedioTecnico;
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
    .promedio {
        font-size: 300%;
    }
</style>