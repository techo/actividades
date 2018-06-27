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
        <div v-show="warning" class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-warning"></i>{{ mensaje }}</h4>
            <p v-show="link !== ''"><a :href="link">Descargar el registro de errores</a></p>
        </div>
    </div>
</template>

<script>
    export default {
        name: "inscripciones-mensajes",
        created(){
            Event.$on('mensaje-error', this.mostrarError);
            Event.$on('mensaje-success', this.mostrarSuccess);
            Event.$on('mensaje-warning', this.mostrarWarning);
        },
        data(){
          return {
              error: false,
              success: false,
              warning: false,
              mensaje: '',
              link: ""
          }
        },
        methods:{
            mostrarError: function (mensaje) {
                this.mensaje = (mensaje.mensaje) ? mensaje.mensaje : mensaje; //6x la palabra "mensaje", algo no está bien
                this.error = true;
                this.success = false;
                this.warning = false;
                this.link = '';
            },
            mostrarSuccess: function (mensaje) {
                this.mensaje = (mensaje.mensaje) ? mensaje.mensaje : mensaje;
                this.success = true;
                this.error = false;
                this.warning = false;
                this.link = '';
            },
            mostrarWarning: function (data) {
                this.mensaje = data.mensaje;
                this.link = data.log_link;
                this.success = false;
                this.error = false;
                this.warning = true;
            }
        }
    }
</script>

<style scoped>

</style>