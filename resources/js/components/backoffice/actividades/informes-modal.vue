<template>
    <div :class="{ 'modal':true, 'fade': true }" :style="{}" id="inscribir-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()" >
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.informe_cierre') }}</h4>
                </div>
                <div class="modal-body">

                    <actividad-informe-cierre-form :key="informe.idActividadInformeCierre" :disabled="true"  :actividad="actividad" :informe="informe" :edicion="true" @saved="guardar" >

                    </actividad-informe-cierre-form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import vSelect from 'vue-select';
import Simplert from 'vue2-simplert';
import { debounce } from 'lodash';

export default {
    components: { 'v-select': vSelect },
    props: [ 'actividad'],
    data: function () {
        return {
            display: false,
            informe: {},
        }
    },
    mounted() {
        Event.$on('informes:crear', this.show);
        Event.$on('informes:editar', this.editar);
    },
    methods: {
        editar(p) {
            this.informe = p; 
            this.show();
        },
        guardar(informe) {
            this.cancelar();
            this.$emit('refrescar');
        },
        show: function () { 
            $('#inscribir-modal').modal('show'); //sino pasan cosas raras con el scroll
        },
        hide: function () { 
            $('#inscribir-modal').modal('hide');
        },
        reset: function () {
            for (let field in this.form) {
                this.form[field] = null;
            }
            this.reset_errors();
        },
        reset_errors: function () {
            for (let field in this.errors) {
                this.errors[field] = null;
                delete this.errors[field];
            }
        },
        cancelar() {
            this.reset();
            this.reset_errors();
            this.hide();
        },
    }
}
</script>