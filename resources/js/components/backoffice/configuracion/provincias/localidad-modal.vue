<template>
    <div :class="{ 'modal': true, 'fade': true }" :style="{}" id="localidad-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Nueva Segunda División</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idLocalidad" class="callout callout-danger">
                        <p v-text="errors.idLocalidad[0]"></p>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.rol }">
                                <label for="nombre">Nombre</label>
                                <input v-model="form.nombre" name="nombre" id="nombre"  type="text" class="form-control" required>
                                <span v-if="errors.nombre" v-text="errors.nombre[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                </div>

            <div class="modal-footer">
                <button ref="cancelar" class="btn" @click="cancelar()">Cancelar</button>
                <button ref="eliminar" v-show="editando" class="btn btn-danger"
                    @click.prevent="confirmar()">Eliminar</button>
                <button ref="guardar" class="btn btn-primary" @click.prevent="guardar()">Guardar</button>
            </div>

        </div>
        </div>
    </div>
</div></template>

<script>

import vSelect from 'vue-select';

export default {
    components: { 'v-select': vSelect },
    props: ['idProvincia'],
    data: function () {
        return {
            display: false,
            provincia: null,
            form: {
                nombre: "",
                idProvincia: this.idProvincia,
                idLocalidad: null,
            },
            errors: {}
        }
    },
    mounted() {
        Event.$on('localidad:crear', this.show);
        Event.$on('localidad:editar', this.editar);
    },
    watch: {
        provincia(v, vv) {
            if (v) this.form.idProvincia = v.id
        }
    },
    computed: {
        editando() {
            if (this.form.id)
                return true
            return false
        }
    },
    methods: {
        guardar() {
            if (this.form.id)
                this.update();
            else
                this.store();
        },
        store() {
            axios.post('/admin/ajax/configuracion/provincias/' + this.form.idProvincias + '/localidades/crear', this.form)
                .then((datos) => {
                    Event.$emit('localidad:refrescar');
                    location.reload();
                    form.reload();
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        update() {
            axios.put('/admin/ajax/configuracion/provincias/' + this.form.idProvincia + '/localidades/' + this.form.id, this.form)
                .then((datos) => {
                    Event.$emit('localidad:refrescar');
                    location.reload();
                    form.reload();
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        eliminar() {
            axios.delete('/admin/ajax/configuracion/provincias/' + this.idProvincia + '/localidades/' + this.form.id, this.form)
                .then((datos) => {
                    Event.$emit('localidad:refrescar');
                    location.reload();
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            console.log("holas");
            this.show();
            axios.get('/admin/ajax/configuracion/provincias/' + this.idProvincia + '/localidades/' + p.id)
                .then((datos) => {
                    this.form = datos.data;
                    this.provincia = datos.data.provincia;
                }).catch((error) => { debugger; });
        },
        show: function () {
            $('#localidad-modal').modal('show'); //sino pasan cosas raras con el scroll
        },
        hide: function () {
            $('#localidad-modal').modal('hide');
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
        confirmar() {
            this.$refs.confirmar.openSimplert({
                title: 'Eliminar Registro',
                message: "Estás por eliminar este registro, de todas maneras no se eliminará si tiene actividades relacionadas. ¿Deseas continuar?",
                useConfirmBtn: true,
                isShown: true,
                disableOverlayClick: true,
                customClass: 'confirmar',
                customCloseBtnText: 'CANCELAR',
                customCloseBtnClass: 'btn btn-default',
                customConfirmBtnText: 'Si, borrar',
                customConfirmBtnClass: 'btn btn-danger',
                onConfirm: function () {
                    this.$parent.eliminar();
                }
            })
        },
        onSearch: _.debounce(function (text, loading) {
            if (text.length > 3) {
                loading(true);
                axios.get('/ajax/configuracion/provincias/localidades?nombre=' + text)
                    .then((datos) => {
                        this.provincias = datos.data.data;
                        loading(false);
                    })
                    .catch((error) => { loading(false); });
            }
        }, 400),

    }
}
</script>