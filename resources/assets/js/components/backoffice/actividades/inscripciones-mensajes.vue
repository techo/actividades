<template>
    <div>
        <div v-show="success" class="callout callout-success">
            <h4>{{ mensaje }}</h4>
        </div>
        <!--<simplert ref="loading"></simplert>-->

        <div v-show="error" class="callout callout-danger">
            <h4>{{ mensaje }}</h4>
        </div>
    </div>
</template>

<script>
    export default {
        name: "inscripciones-mensajes",
        created(){
            Event.$on('mensaje-error', this.mostrarError);
            Event.$on('mensaje-success', this.mostrarSuccess);
        },
        data(){
          return {
              error: false,
              success: false,
              mensaje: ""
          }
        },
        methods:{
            mostrarError: function (mensaje) {
                this.mensaje = mensaje;
                this.error = true;
                this.success = false;
            },
            mostrarSuccess: function (mensaje) {
                this.mensaje = mensaje;
                this.success = true;
                this.error = false;
            }
        }
    }
</script>

<style scoped>

</style>