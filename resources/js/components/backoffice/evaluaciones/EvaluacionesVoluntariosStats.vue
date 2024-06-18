<template>
    <div>
        <div class="row">
            <div class="col-md-6">
                <h4>{{ $t('backend.evaluators') }}</h4>
            </div>
            <div class="col-md-6">
                <h4 class="text-center">{{ $t('backend.evaluation_percentage') }}</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h1><strong>{{ evaluaron }}</strong></h1>
                        <h4 class="mt-0">{{ $t('backend.evaluated') }}</h4>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h1><strong>{{ pendientesEvaluar }}</strong></h1>
                        <h4 class="mt-0">{{ $t('backend.pending_evaluation') }}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4>{{ $t('backend.average_evaluations') }}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h1 class="promedio"><strong>{{ promedioSocial }}</strong></h1>
                        <h5>{{ $t('backend.social1') }}</h5>
                    </div>
                    <div class="col-md-6">
                        <h1 class="promedio"><strong>{{ promedioTecnico }}</strong></h1>
                        <h5>{{ $t('backend.technical1') }}</h5>
                    </div>
                    <div class="col-md-6">
                        <h1 class="promedio"><strong>{{ promedioGenero }}</strong></h1>
                        <h5>{{ $t('backend.gender_perspective') }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center">
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

    export default {
        name: "evaluaciones-voluntarios-stats",
        props: [ 'id' ],
        components: { knob },
        data(){
            return {
                evaluaron: 0,
                promedioSocial: 0,
                promedioTecnico: 0,
                promedioGenero: 0,
                loading: true,
                presentes: 0,
            }
        },
        computed: {
          porcentajeEvaluaciones: function () {
              if(this.loading) return 0;
              if(this.presentes === 0) return 0;

              let porcentaje = Math.round(this.evaluaron * 100 / this.presentes);
              Event.$emit("knob-eval-voluntarios-upd", porcentaje);
              return porcentaje;
          },
          totalPresentes: function () {
              return this.presentes;
          },
          pendientesEvaluar: function () {
              if(this.loading) return 0;
              return this.presentes - this.evaluaron;
          }
        },
        mounted(){
            
            this.getStats();
        },
        methods: {
            getStats() {
                let url = "/admin/ajax/actividades/" + this.id + "/evaluaciones/voluntarios/stats";
                axios.get(url)
                    .then((datos) => { 
                        this.presentes = datos.data.presentes;
                        this.evaluaron = datos.data.evaluaron;
                        this.promedioSocial = datos.data.promedioSocial;
                        this.promedioTecnico = datos.data.promedioTecnico;
                        this.promedioGenero = datos.data.promedioGenero;
                        Event.$emit('stats-voluntarios-loaded');
                        this.loading = false;
                    }).catch((error) => { debugger; });
            }
        }
    }
</script>

<style scoped>
    .promedio {
        font-size: 300%;
    }
</style>