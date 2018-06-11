<template>
    <div class="btn-group" role="group" aria-label="toolbar">
        <button type="button" class="btn btn-default" :class="{'disabled': disabled}" @click="this.mostrarRolModal">Rol</button>
        <button type="button" class="btn btn-default" :class="{'disabled': disabled}" @click="this.mostrarGrupoModal">Grupo</button>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" :class="{'disabled': disabled}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Asistencia
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a @click="cambiarAsistencia(1, $event)">Presente</a></li>
                <li><a @click="cambiarAsistencia(0, $event)">Ausente</a></li>
            </ul>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" :class="{'disabled': disabled}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Estado
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a @click="cambiarEstado('Confirmado', $event)">Confirmado</a></li>
                <li><a @click="cambiarEstado('Sin Confirmar', $event)">Sin Confirmar</a></li>
                <li><a @click="cambiarEstado('Sin Interés', $event)">Sin Interés</a></li>
                <li><a @click="cambiarEstado('Sin Contactar', $event)">Sin Contactar</a></li>
            </ul>
        </div>
        <inscripciones-rol-modal></inscripciones-rol-modal>
        <inscripciones-grupo-modal></inscripciones-grupo-modal>
    </div>
</template>

<script>
    export default {
        name: "inscripciones-toolbar",
        data() {
           return {
                disabled: true
            }
        },
        created(){
            Event.$on('checkbox-toggled', this.toggleButtons);
        },
        methods: {
            mostrarRolModal: function () {
                if(this.disabled) return;
                Event.$emit('show-rol-modal');
            },
            mostrarGrupoModal: function () {
                if(this.disabled) return;
                Event.$emit('show-grupo-modal');
            },
            cambiarEstado: function (estado, event) {
                Event.$emit('cambiar-estado', estado);
            },
            cambiarAsistencia: function (asistencia, event) {
                Event.$emit('cambiar-asistencia', asistencia);
            },
            toggleButtons: function (info) {
                this.disabled = (info.count === 0);
            }
        }
    }
</script>

<style scoped>

</style>