<template>
    <footer class="main-footer" style="position:fixed; bottom: 0; width: 100%">
        <!-- To the right -->
        <div>
            <button class="btn btn-primary" v-show="readonly" @click="this.editar"><i class="fa fa-edit"></i> Editar
            </button>
            <button class="btn btn-danger" v-show="readonly"><i class="fa fa-trash"></i> Borrar</button>

            <button class="btn btn-success" v-show="!readonly"><i class="fa fa-save"></i> Guardar</button>
            <button class="btn btn-secondary" v-show="!readonly" @click="edicion=false"><i
                    class="fa fa-arrow-circle-left"></i> Cancelar
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
                this.$emit('editar');
                this.edicion = true;
            },

            cancelar: function () {

            }
        }
    }
</script>

<style scoped>

</style>