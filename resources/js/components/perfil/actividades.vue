<template>
    <div>
      <div class="alert alert-warning" v-show='borro'>
        <strong>{{ $t('frontend.unenroll_ok') }}</strong>
      </div>
      <div class="alert alert-warning" v-show='!inscripciones.length'>
        <strong>{{ $t('frontend.enrollment_empty') }}</strong>
      </div>
        <div class="row">
            <div
                class="col-12 col-sm-6 col-md-4 mb-3"
                v-for="inscripcion in inscripciones"
                v-bind:key="inscripcion.idInscripcion"
            >
                <tarjeta v-bind:inscripcion="inscripcion"></tarjeta>
            </div>
            <button
                v-if="actividadPasada && periodoDeEvaluacionYaComenzo && inscripcionPresente"
                class="btn btn-sm btn-info"
                @click="ir_a_evaluar"
        >
            {{ __('frontend.view_evaluations') }}
        </button>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import Tarjeta from './tarjeta';

    export default {
        name: "mis-inscripciones",

        data () {
            return {
                act: '',
                inscripciones: [],
                borro: false
            }
        },
        components: {tarjeta: Tarjeta},
        mounted: function() {
            this.traer_inscripciones()
        },
        methods: {
            traer_inscripciones: function() {
                axios.get('/ajax/usuario/inscripciones?date=')
                    .then(response => {
                    this.inscripciones = response.data.data
                })
            }
        }

    }
</script>

<style>

</style>