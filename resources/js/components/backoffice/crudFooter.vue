<template>
    <footer class="main-footer" style="position:fixed; bottom: 0; width: 100%">
        <!-- To the right -->
        <simplert ref="confirmar"></simplert>
        <div class="row hidden-sm">
            <div class="col-sm-12 col-md-4">
                <button class="btn btn-default" @click="cancelar">
                    <i class="fa fa-arrow-circle-left"></i> Volver al listado
                </button>
            </div>
            <div class="col-sm-12 col-md-1">
                <button type="button" class="btn btn-primary" v-show="readonly && compartir" data-toggle="modal" data-target="#compartirModal">
                    <i class="fa fa-share-alt"></i>  Compartir
                </button>
            </div>
            <div class="col-sm-12 col-md-2">
                <button class="btn btn-primary" v-show="readonly && canClonar" @click="clonar">
                    <i class="fa fa-clone"></i>
                    Clonar Actividad
                </button>
            </div>
            <div class="col-sm-12 col-md-5">
                <button class="btn btn-primary" v-show="readonly && canFusionar" @click="fusionar">
                    <i class="fa fa-random"></i>
                    Fusionar
                </button>
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
        </div>
        <div class="row show-sm">
            <div class="col-sm-12">
                <button class="btn btn-default btn-sm btn-block" @click="cancelar">
                    <i class="fa fa-arrow-circle-left"></i> Volver al listado
                </button>
            </div>
            <div class="col-sm-12">
                <button type="button" class="btn btn-sm btn-primary btn-block" v-show="readonly && compartir" data-toggle="modal" data-target="#compartirModal">
                    <i class="fa fa-share-alt"></i>  Compartir
                </button>
            </div>
            <div class="col-sm-12">
                <button class="btn btn-primary btn-sm btn-block" v-show="readonly && canClonar" @click="clonar">
                    <i class="fa fa-clone"></i>
                    Clonar Actividad
                </button>
            </div>
            <div class="col-sm-12">
                <button class="btn btn-primary btn-sm btn-block" v-show="readonly && canEditar" @click="editar">
                    <i class="fa fa-edit"></i>
                    Editar
                </button>
            </div>
            <div class="col-sm-12">
                <button class="btn btn-danger btn-sm btn-block" v-show="readonly && canBorrar" @click="eliminar">
                    <i class="fa fa-trash"></i>
                    Borrar
                </button>
            </div>
            <div class="col-sm-12">
                <button class="btn btn-success btn-sm btn-block" v-show="!readonly" @click="this.guardar">
                    <i class="fa fa-save"></i>
                    Guardar
                </button>
            </div>
            <div class="col-sm-12">
              <button class="btn btn-secondary btn-sm btn-block" v-show="!readonly" @click="cancelar">
                  <i class="fa fa-ban"></i>
                  Cancelar
              </button>
            </div>
        </div>
    </footer>
</template>

<script>
    export default {
        name: "crudFooter",
        props: ['edicion', 'compartir', 'cancelarUrl', 'canEditar', 'canBorrar', 'canClonar', 'canFusionar'],
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
                    title: 'Eliminar Registro',
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

            fusionar: function () {
                Event.$emit('fusionar');
            },

            guardar: function () {
                Event.$emit('guardar');
                this.readonly = true;
            }
        }
    }
</script>

<style scoped>
 @media (max-width: 768px){
     .hidden-sm {
         display: none;
     }

     .show-sm {
         display: block;
     }

     .show-sm .col-sm-12 {
         margin: 5px 5px;
     }
 }

 @media (min-width: 769px){
     .hidden-sm {
         display: block;
     }

     .show-sm {
         display: none;
     }
 }
</style>