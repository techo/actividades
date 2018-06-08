<template>
    <div class="btn-group" role="group" aria-label="toolbar">
        <button type="button" class="btn btn-default" @click="this.mostrarRolModal">Asignar Rol</button>
        <button type="button" class="btn btn-default">Asignar Grupo</button>
        <button type="button" class="btn btn-default">Cambiar Estado</button>
        <inscripciones-rol-modal></inscripciones-rol-modal>
    </div>
</template>

<script>
    export default {
        name: "inscripciones-toolbar",
        created(){
            // Event.$on('rol-asignado', this.asignarRol);
        },
        methods: {
            mostrarRolModal: function () {
                Event.$emit('show-rol-modal');
            },
            // asignarRol: function (rol) {
            //     debugger
            //     console.log(this.$refs.inscripcionesVuetable.selectedTo);
            //     console.log(rol);
            // }
        }
    }
</script>

<style scoped>

</style>