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

    export default {
        name: "evaluaciones-actividad-stats",
        props: [ 'id' ],
        components: { knob },
        data(){
            return {
                evaluaron: 0,
                promedio: 0,
                loading: true,
                presentes: 0,
            }
        },
        computed: {
          porcentajeEvaluaciones: function () {
              if(this.loading) return 0;
              if(this.presentes === 0) return 0;

              let porcentaje = Math.round(this.evaluaron * 100 / this.presentes);
              Event.$emit("knob-eval-actividad-upd", porcentaje);
              return porcentaje;
          },
          pendientesEvaluar: function () {
              if(this.loading) return 0;
              return this.presentes - this.evaluaron;
            }
        },
        created(){
            this.getStats();
        },
        methods: {
            getStats: function () {
                axios.get("/admin/ajax/actividades/" + this.id + "/evaluaciones/stats")
                    .then((datos) => { 
                        this.evaluaron = datos.data.evaluaron;
                        this.promedio = datos.data.promedio;
                        this.presentes = datos.data.presentes;
                        Event.$emit('stats-actividad-loaded');
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