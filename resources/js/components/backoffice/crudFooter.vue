<template>
    <footer class="main-footer" style="margin: 0">
        <!-- To the right -->
        <simplert ref="confirmar"></simplert>
        <div class="row hidden-sm">
            <div class="col-sm-12 col-md-4">
                <button class="btn btn-default" @click="cancelar">
                    <i class="fa fa-arrow-circle-left"></i> {{ $t('backend.back_to_list') }}
                </button>
            </div>

            <div class="col-sm-12 col-md-8" style="display: flex; justify-content: flex-end;">
                <a class="btn btn-primary" style="margin-right: 4px;" v-show="readonly && watchUrl" :href="watchUrl" target="_blank">
                    <i class="fa fa-eye"></i>  {{ $t('backend.view') }}
                </a>
                <button class="btn btn-primary" style="margin-right: 4px;" v-show="readonly && compartir" data-toggle="modal" data-target="#compartirModal">
                    <i class="fa fa-share-alt"></i>  {{ $t('backend.share') }}
                </button>
                <button class="btn btn-primary" style="margin-right: 4px;" v-show="readonly && canClonar" @click="clonar">
                    <i class="fa fa-clone"></i>
                    {{ $t('backend.clone_activity') }}
                </button>
                <button class="btn btn-secondary" style="margin-right: 4px;" v-show="!readonly" @click="cancelar">
                    <i class="fa fa-ban"></i>
                    {{ $t('backend.cancel') }}
                </button>
                <button class="btn btn-primary" style="margin-right: 4px;" v-show="readonly && canFusionar" @click="fusionar">
                    <i class="fa fa-random"></i>
                    {{ $t('backend.fuse') }}
                </button>
                <button class="btn btn-primary" style="margin-right: 4px;" v-show="readonly && canEditar" @click="editar">
                    <i class="fa fa-edit"></i>
                    {{ $t('backend.edit') }}
                </button>
                <button class="btn btn-danger" style="margin-right: 4px;" v-show="readonly && canBorrar" @click="eliminar">
                    <i class="fa fa-trash"></i>
                    {{ $t('backend.delete') }}
                </button>
                <button class="btn btn-success" style="margin-right: 4px;" v-show="!readonly" @click="this.guardar">
                    <i class="fa fa-save"></i>
                    {{ $t('backend.save') }}
                </button>
            </div>
        </div>
        <div class="row show-sm">
            <div class="col-sm-12">
                <button class="btn btn-default btn-sm btn-block" @click="cancelar">
                    <i class="fa fa-arrow-circle-left"></i> {{ $t('backend.back_to_list') }}
                </button>
            </div>
            <div class="col-sm-12">
                <button type="button" class="btn btn-sm btn-primary btn-block" v-show="readonly && compartir" data-toggle="modal" data-target="#compartirModal">
                    <i class="fa fa-share-alt"></i>  {{ $t('backend.share') }}
                </button>
            </div>
            <div class="col-sm-12">
                <button class="btn btn-primary btn-sm btn-block" v-show="readonly && canClonar" @click="clonar">
                    <i class="fa fa-clone"></i>
                    {{ $t('backend.clone_activity') }}
                </button>
            </div>
            <div class="col-sm-12">
                <button class="btn btn-primary btn-sm btn-block" v-show="readonly && canEditar" @click="editar">
                    <i class="fa fa-edit"></i>
                    {{ $t('backend.edit') }}
                </button>
            </div>
            <div class="col-sm-12">
                <button class="btn btn-danger btn-sm btn-block" v-show="readonly && canBorrar" @click="eliminar">
                    <i class="fa fa-trash"></i>
                    {{ $t('backend.delete') }}
                </button>
            </div>
            <div class="col-sm-12">
                <button class="btn btn-success btn-sm btn-block" v-show="!readonly" @click="this.guardar">
                    <i class="fa fa-save"></i>
                    {{ $t('backend.save') }}
                </button>
            </div>
            <div class="col-sm-12">
              <button class="btn btn-secondary btn-sm btn-block" v-show="!readonly" @click="cancelar">
                  <i class="fa fa-ban"></i>
                  {{ $t('backend.cancel') }}
              </button>
            </div>
        </div>
    </footer>
</template>

<script>
    export default {
        name: "crudFooter",
        props: ['edicion', 'compartir', 'cancelarUrl', 'canEditar', 'canBorrar', 'canClonar', 'canFusionar', 'watchUrl'],
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