<template>
    <div>
      <div class="alert alert-warning" v-show="borro">
        <strong>{{ $t('frontend.unenroll_ok') }}</strong>
      </div>
      <div class="alert alert-light text-muted" v-show="cargado && !inscripciones.length">
        {{ mensajeVacio }}
      </div>
      <div class="row">
        <div
          class="col-12 col-sm-6 col-md-4 mb-3"
          v-for="inscripcion in inscripciones"
          v-bind:key="inscripcion.idInscripcion || inscripcion.idActividad"
        >
          <tarjeta v-bind:inscripcion="inscripcion"></tarjeta>
        </div>
      </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import Tarjeta from './tarjeta';

    export default {
        name: "mis-inscripciones",
        props: {
            // '' = próximas (default). 'pasadas' = históricas.
            date: {
                type: String,
                default: '',
            },
        },
        data () {
            return {
                inscripciones: [],
                borro: false,
                cargado: false,
            }
        },
        components: { tarjeta: Tarjeta },
        computed: {
            mensajeVacio() {
                return this.date === 'pasadas'
                    ? this.$t('frontend.no_past_activities')
                    : this.$t('frontend.enrollment_empty');
            },
        },
        mounted: function() {
            this.traer_inscripciones()
        },
        methods: {
            traer_inscripciones: function() {
                axios.get('/ajax/usuario/inscripciones?date=' + this.date)
                    .then(response => {
                        this.inscripciones = response.data.data
                        this.cargado = true
                    })
            }
        }
    }
</script>

<style scoped>
</style>
