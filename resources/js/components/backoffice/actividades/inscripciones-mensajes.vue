<template>
        <div v-show="display" :class="{ 
            'alert': true, 
            'alert-dismissible': true, 
            'alert-danger': danger, 
            'alert-success': success, 
            'alert-warning': warning
        }">
            <button type="button" class="close" @click="display = false" aria-hidden="true">×</button>
            <h4><i class="icon fa fa-warning"></i>{{ titulo }}</h4>
            {{ mensaje }}
            <p v-show="link !== ''"><a :href="link">Descargar el registro de errores</a></p>
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
              display: false,
              danger: false,
              success: false,
              warning: false,
              mensaje: '',
              titulo: '',
              link: ""
          }
        },
        methods:{
            mostrar: function (mensaje) {
                this.display = true;
                this.mensaje = (mensaje.mensaje) ? mensaje.mensaje : mensaje; //6x la palabra "mensaje", algo no está bien
                if (this.warning == true || this.danger == true)
                    return;
                else 
                    setTimeout(() => this.display = false, 2000);
            },
            mostrarError: function (mensaje) {
                this.danger = true;
                this.titulo = 'Error';
                this.mostrar(mensaje);
            },
            mostrarSuccess: function (mensaje) {
                this.success = true;
                this.titulo = 'Éxito';
                this.mostrar(mensaje);
            },
            mostrarWarning: function (mensaje) {
                this.warning = true;
                this.titulo = 'Advertencia';
                this.mostrar(mensaje);
            }
        }
    }
</script>