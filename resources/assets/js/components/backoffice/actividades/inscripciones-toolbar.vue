<template>
    <div class="btn-group" role="group" aria-label="toolbar">
        <button type="button" class="btn btn-default" @click="this.mostrarRolModal">Rol</button>
        <button type="button" class="btn btn-default" @click="this.mostrarGrupoModal">Grupo</button>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Asistencia
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li><a @click="cambiarAsistencia(1, $event)">Presente</a></li>
                <li><a @click="cambiarAsistencia(0, $event)">Ausente</a></li>
            </ul>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
        methods: {
            mostrarRolModal: function () {
                Event.$emit('show-rol-modal');
            },
            mostrarGrupoModal: function () {
                Event.$emit('show-grupo-modal');
            },
            cambiarEstado: function (estado, event) {
                Event.$emit('cambiar-estado', estado);
            },
            cambiarAsistencia: function (asistencia, event) {
                Event.$emit('cambiar-asistencia', asistencia);
            }
        }
    }
</script>

<style scoped>

</style>