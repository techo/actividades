<template>
    <div>
      <div class="row">
        <div class="col-md-4">
          <h2>Tus actividades</h2>
        </div>
      </div>
      <div class="alert alert-warning" v-show='borro'>
        <strong>Te has desinscrito satisfactoriamente de la actividad.</strong>
      </div>
        <div class="row">
            <div class="card-deck text-center">
                <tarjeta
                    v-for="act in actividades"
                    v-bind:actividad="act"
                    v-bind:key="Math.random() + '_' + act.idActividad"
                >
                </tarjeta>
            </div>
        </div>

    </div>
</template>

<script>
    import axios from 'axios';
    import Tarjeta from './tarjeta';

    export default {
        name: "mis-actividades",

        data () {
            return {
                actividades: [],
            }
        },
        components: {tarjeta: Tarjeta},
        mounted: function() {
            this.traer_actividades()
        },
        methods: {
            traer_actividades: function() {
                axios.get('/ajax/usuario/inscripciones').then(response => {
                    this.actividades = response.data
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