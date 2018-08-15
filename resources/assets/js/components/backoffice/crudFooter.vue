<template>
    <footer class="main-footer" style="position:fixed; bottom: 0; width: 100%">
        <!-- To the right -->
        <simplert ref="confirmar"></simplert>
        <div class="col-md-5">
            <button class="btn btn-default" @click="cancelar">
                <i class="fa fa-arrow-circle-left"></i> Volver al listado
            </button>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-primary" v-show="readonly && compartir" data-toggle="modal" data-target="#compartirModal">
                <i class="fa fa-share-alt"></i>  Compartir
            </button>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary" v-show="readonly && canClonar" @click="clonar">
                <i class="fa fa-clone"></i>
                Clonar Actividad
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary" v-show="readonly && canEditar" @click="editar">
                <i class="fa fa-edit"></i>
                Editar
            </button>
            <button class="btn btn-danger" v-show="readonly && canBorrar" @click="eliminar">
                <i class="fa fa-trash"></i>
                Borrar
            </button>

            <button class="btn btn-success" v-show="!readonly" @click="this.guardar">
                <i class="fa fa-save"></i>
                Guardar
            </button>
            <button class="btn btn-secondary" v-show="!readonly" @click="cancelar">
                <i class="fa fa-ban"></i>
                Cancelar
            </button>
        </div>

    </footer>
</template>

<script>
    export default {
        name: "crudFooter",
        props: ['edicion', 'compartir', 'cancelarUrl', 'canEditar', 'canBorrar', 'canClonar'],
        data: function () {
            return {
                readonly: (this.edicion != "1")
            }
        },
        created: function() {
            Event.$on('error', this.error);
            Event.$on('success', this.success);
        },
        methods: {
            error: function () {
                this.readonly = false;
            },
            success: function() {
                this.readonly = true;
            },
            editar: function () {
                Event.$emit('editar');
                this.readonly = false;
            },

            cancelar: function () {
                location.href= this.cancelarUrl;
            },
            eliminar: function () {
                let self = this;
                self.$refs.confirmar.openSimplert({
                    title: 'Borrar Actividad',
                    message: "Estás por eliminar este registro, se borrará permanentemente y no podrá recuperarse. ¿Deseas continuar?",
                    useConfirmBtn: true,
                    isShown: true,
                    disableOverlayClick: true,
                    customClass: 'confirmar',
                    customCloseBtnText: 'CANCELAR',
                    customCloseBtnClass: 'btn btn-default',
                    customConfirmBtnText: 'Si, borrar',
                    customConfirmBtnClass: 'btn btn-danger',
                    onConfirm: function () {
                        Event.$emit('eliminar');
                    }
                })
            },
            clonar: function () {
                let self = this;
                self.$refs.confirmar.openSimplert({
                    title: 'Clonar Actividad',
                    message: "Estás por duplicar esta actividad, se creará una copia de la información y de los grupos que " +
                    "hayan sido creados.  Los participantes no quedarán inscriptos en la copia. ¿Desea continuar?",
                    useConfirmBtn: true,
                    isShown: true,
                    disableOverlayClick: true,
                    customClass: 'confirmar',
                    customCloseBtnText: 'CANCELAR',
                    customCloseBtnClass: 'btn btn-default',
                    customConfirmBtnText: 'Si, clonar',
                    customConfirmBtnClass: 'btn btn-primary',
                    onConfirm: function () {
                        Event.$emit('clonar');
                    }
                })
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