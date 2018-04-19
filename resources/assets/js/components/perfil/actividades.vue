<template>
    <div>
      <div class="row">
        <div class="col-md-4">
          <h2>Tus inscripciones</h2>
        </div>
      </div>
      <div class="alert alert-warning" v-show='borro'>
        <strong>Te has desinscrito satisfactoriamente de la actividad.</strong>
      </div>
      <div class="alert alert-warning" v-show='!inscripciones.length'>
        <strong>Todavia no estas inscripto a ninguna actividad.</strong>
      </div>
        <div class="row">
            <tarjeta
                v-for="inscripcion in inscripciones"
                v-bind:inscripcion="inscripcion"
                v-bind:key="inscripcion.idInscripcion"
            >
            </tarjeta>
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
                axios.get('/ajax/usuario/inscripciones').then(response => {
                    this.inscripciones = response.data
                })
            }
        }

    }
</script>

<style>
.confirmar: {
    min-width: 800px;
}
.confirmar > .simplert_content: {
    min-width: 90% !important;
    color: red;
}
</style>