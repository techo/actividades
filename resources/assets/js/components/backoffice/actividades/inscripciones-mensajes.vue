<template>
    <div>
        <div v-show="success" class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-check"></i>{{ mensaje }}</h4>
        </div>
        <!--<simplert ref="loading"></simplert>-->
        <div v-show="error" class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-warning"></i>{{ mensaje }}</h4>
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