<template>
    <div :class="{ 'modal': true, 'fade': true }" :style="{}" id="reunion-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.crear_reunion') }}</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idReunion" class="callout callout-danger">
                        <p v-text="errors.idReunion[0]"></p>
                    </div>

                    
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.rol }">
                                <label for="nombre">{{ $t('backend.name') }}</label>
                                <input v-model="form.nombre" name="nombre" type="text" class="form-control" required>
                                <span v-if="errors.nombre" v-text="errors.nombre[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.despliegue }">
                                <label for="despliegue">{{ $t('backend.deployment') }}</label>
                                <select v-model="form.despliegue" name="despliegue" class="form-control" required>
                                    <option value="Oficina" :selected="form.despliegue == 'Oficina'">{{ $t('backend.office') }}</option>
                                    <option value="Comunidad" :selected="form.despliegue == 'Comunidad'">{{ $t('backend.community') }}</option>
                                </select>
                                <span v-if="errors.despliegue" v-text="errors.despliegue[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.fecha }">
                                <label for="fecha">{{ $t('backend.date') }}</label>
                                <input v-model="form.fecha" name="fecha" type="date" class="form-control"
                                    required>
                                <span v-if="errors.fecha" v-text="errors.fecha[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div style="position: relative;">
                                <label for="descripcion">{{ $t('backend.people') }}</label>
                                <vue-tags-input
                                    v-model="tagPersona"
                                    :tags="personasMiembros"
                                    :autocomplete-items="filteredPersonasTags"
                                    :add-only-from-autocomplete="true"
                                    @tags-changed="updatePersonasMiembros"
                                    @input="onSearch"
                                    placeholder="Buscar persona..."
                                    class="w-100"
                                    style="width: 100%;"
                                />
                                <i class="fa fa-spinner fa-spin" v-if="loadingPersonas"></i>
                                <span v-if="loadingPersonas"
                                    class="spinner-border spinner-border-sm text-secondary"
                                    style="position: absolute; top: 10px; right: 10px;">
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.meta }">
                                <label for="descripcion">{{ $t('backend.contenido_reunion') }}</label>
                                <tinymce-editor 
                                    v-model="form.descripcion" 
                                    :init="{
                                        menubar: 'false',
                                        file_picker_callback: tiny_mce_filemanager_callback,
                                        relative_urls: false,
                                        resize: true,
                                    }"
                                    toolbar="undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image" 
                                    plugins="paste autoresize image preview paste link"
                                ></tinymce-editor>
                                <span v-if="errors.descripcion" v-text="errors.descripcion[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.meta }">
                                <label for="descripcion">{{ $t('backend.compromisos') }}</label>

                                <textarea v-model="form.compromisos" name="compromisos" class="form-control" rows="3" required></textarea>
                                <span v-if="errors.compromisos" v-text="errors.compromisos[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                   

                    
                </div>

                

                <div class="modal-footer text-center">
                    <div v-if="guardado" class="row">
                        <p class="text-center bg-success">
                            {{ $t('backend.changes_saved') }}
                        </p>
                    </div>
                    <button ref="cancelar" class="btn" @click="cancelar()">{{ $t('backend.cancel') }}</button>
                    <button ref="eliminar" v-show="editando" class="btn btn-danger"
                        @click.prevent="confirmar()">{{ $t('backend.eliminate') }}</button>
                    <button ref="guardar" class="btn btn-primary" @click.prevent="guardar()">{{ $t('backend.save') }}</button>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import tinymceEditor from '@tinymce/tinymce-vue';
import vSelect from 'vue-select';
import Simplert from 'vue2-simplert';
import VueTagsInput from '@johmun/vue-tags-input';

export default {
    components: { tinymceEditor, 'v-select': vSelect , VueTagsInput},
    props: ['idEquipo'],
    data: function () {
        return {
            display: false,
            persona: null,
            archivo_carta_compromiso: null,
            nombre_carta_compromiso: '',
            archivo_plan_de_trabajo: null,
            personas: [],
            form: {
                idEquipo: this.idEquipo,
                nombre: null,
                despliegue: null,
                fecha: null,
                descripcion: null,
                compromiso: null,
                personas: [],
            },
            personasMiembros: [],
            tags: [],
            tag: '',
            tagPersonas: '',
            errors: {},
            guardado: false,
            tagPersona: '',
            personasMiembros: [], // [{ text: 'Juan Pérez', id: 5 }, ...]
            filteredPersonasTags: [],
            loadingPersonas: false,
        }
    },
    mounted() {
        Event.$on('reunion:crear', this.show);
        Event.$on('reunion:editar', this.editar);
        this.guardado = false;
    },
    watch: {
        persona(v, vv) {
            if (v) this.form.idPersona = v.idPersona
        }
    },
    computed: {
        editando() {
            if (this.form['idReunion'])
                return true
            return false
        },

    },
    methods: {
        guardar() {
            this.form.personas = this.personasMiembros.map(p => p.id);
            if (this.form['idReunion'])
                this.update();
            else
                this.store();
        },
        store() {
            axios.post('/admin/ajax/equipos/' + this.idEquipo + '/reuniones/crear', this.form)
                .then((datos) => {
                    this.guardado = true;
                    setTimeout(() => {
                        this.guardado = false;
                        this.$emit('actualizar');
                        this.cancelar();
                    }, 2000);
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });

        },

        update() {
            axios.put('/admin/ajax/equipos/' + this.idEquipo + '/reuniones/' + this.form.idReunion, this.form)
                .then((datos) => {
                    this.guardado = true;
                    setTimeout(() => {
                        this.guardado = false;
                        this.$emit('actualizar');
                        this.cancelar();
                    }, 2000);
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        eliminar() {
            axios.delete('/admin/ajax/equipos/' + this.idEquipo + '/integrante/' + this.form.idIntegrante, this.form)
                .then((datos) => {
                    Event.$emit('integrantes:refrescar');
                    location.reload();
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            axios.get('/admin/ajax/equipos/' + this.idEquipo + '/reuniones/' + p.idReunion)
                .then((datos) => {
                    this.form = datos.data.data;
                    this.personasMiembros = this.form.personas;
                    this.form.fecha = moment(this.form.fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
                }).catch((error) => { debugger; });
            
                this.show();
        },
        show: function () {
            this.reset();
            $('#reunion-modal').modal('show');
        },
        hide: function () {
            $('#reunion-modal').modal('hide');
        },
        reset: function () {
            for (let field in this.form) {
                this.form[field] = null;
            }
            this.form.idEquipo = this.idEquipo;
            this.persona = null;
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
                    this.$parent.eliminar();
                }
            })
        },
        tiny_mce_filemanager_callback(callback, value, meta) {
                //gracias a esto ❤ https://github.com/UniSharp/laravel-filemanager/issues/759 
                let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                let y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
                let cmsURL = '/laravel-filemanager?editor=tinymce5&field_name=' + value;
                if (meta.filetype == 'image') { cmsURL = cmsURL + "&type=Images"; } 
                else { cmsURL = cmsURL + "&type=Files"; }

                tinyMCE.activeEditor.windowManager.openUrl({
                    url : cmsURL,
                    title : 'Administrador de archivos',
                    width : x * 0.8,
                    height : y * 0.8,
                    resizable : "yes",
                    close_previous : "no",
                    onMessage: (api, message) => {
                        callback(message.content);
                    }
                });
            },
        onSearch: _.debounce(function (text) {
                if (text.length > 3) {
                    this.loadingPersonas = true;
                    axios.get('/ajax/coordinadores?coordinador=' + text)
                    .then((response) => {
                        this.filteredPersonasTags = response.data.data.map(persona => ({
                            text: persona.nombre,
                            id: persona.idPersona,
                        }));
                    })
                    .finally(() => {
                        this.loadingPersonas = false;
                    });
                }
            }, 400),

            updatePersonasMiembros(newTags) {
                this.personasMiembros = newTags;
            }

    }
}
</script>