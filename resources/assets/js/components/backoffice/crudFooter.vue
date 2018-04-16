<template>
    <footer class="main-footer" style="position:fixed; bottom: 0; width: 100%">
        <!-- To the right -->
        <div>
            <button class="btn btn-primary" v-show="readonly" @click="this.editar"><i class="fa fa-edit"></i> Editar
            </button>
            <button class="btn btn-danger" v-show="readonly" @click="this.eliminar"><i class="fa fa-trash"></i> Borrar
            </button>

            <button class="btn btn-success" v-show="!readonly" @click="this.guardar"><i class="fa fa-save"></i> Guardar</button>
            <button class="btn btn-secondary" v-show="!readonly" @click="this.cancelar"><i class="fa fa-arrow-circle-left"></i> Cancelar
            </button>
        </div>
    </footer>
</template>

<script>
    export default {
        name: "crudFooter",
        data: function () {
            return {
                readonly: true
            }
        },
        methods: {
            editar: function () {
                Event.$emit('editar');
                this.readonly = false;
            },

            cancelar: function () {
                location.reload();
            },
            eliminar: function () {
                Event.$emit('eliminar');
            },

            guardar: function () {
                Event.$emit('guardar');
                this.readonly = true;
            }
        }
    }
</script>

<style scoped>

</style>