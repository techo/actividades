<template>
    <div :class="{ 'modal': true, 'fade': true }" :style="{}" id="inscribir-modal">
        <simplert ref="confirmar"></simplert>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">{{ $t('backend.new_member') }}</h4>
                </div>
                <div class="modal-body">

                    <div v-if="errors.idIntegrante" class="callout callout-danger">
                        <p v-text="errors.idIntegrante[0]"></p>
                    </div>

                    <div v-if="!idEquipo" class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.idPersona }">
                                <label for="idPersona">{{ $t('backend.team') }}</label>
                                <input v-model="form.nombreEquipo" name="team" type="text" class="form-control" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.idPersona }">
                                <label for="idPersona">{{ $t('backend.person') }}</label>
                                <v-select :options="personas" @search="onSearch" label="nombre" v-model="persona"
                                    :filterable="false" :selectOnTab="true" v-bind:disabled="!idEquipo"></v-select>
                                <span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div v-if="idEquipo" class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.despliegue }">
                                <label for="despliegue">{{ $t('backend.deployment') }}</label>
                                <select v-model="form.despliegue" name="despliegue" class="form-control" required>
                                    <option value="Oficina" :selected="form.despliegue == 'Oficina'">{{ $t('backend.office') }}</option>
                                    <option value="Comunidad" :selected="form.despliegue == 'Comunidad'">{{ $t('backend.community') }}</option>
                                    <option value="Otras" :selected="form.despliegue == 'Otras'">{{ $t('backend.other') }}</option>
                                </select>
                                <span v-if="errors.despliegue" v-text="errors.despliegue[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.relacion }">
                                <label for="relacion">{{ $t('backend.relationship') }}</label>
                                <select v-model="form.relacion" name="relacion" class="form-control" required>
                                    <option value="Rentado" :selected="form.relacion == 'Rentado'">{{ $t('backend.rented') }}</option>
                                    <option value="Voluntario" :selected="form.relacion == 'Voluntario'">{{ $t('backend.volunteer') }}</option>
                                    <option value="Pasante" :selected="form.relacion == 'Pasante'">{{ $t('backend.intern') }}</option>
                                </select>
                                <span v-if="errors.relacion" v-text="errors.relacion[0]" class="help-block"></span>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.estado }">
                                <label for="estado">{{ $t('backend.state') }}</label>
                                <select v-model="form.estado" name="estado" class="form-control" required>
                                    <option value="1" :selected="form.estado == 1">{{ $t('backend.active') }}</option>
                                    <option value="0" :selected="form.estado == 0">{{ $t('backend.inactive') }}</option>
                                </select>
                                <p
                                    v-if="form.estado == 1 && editando && estadoEraInactivo"
                                    class="help-block text-warning"
                                >
                                    <i class="fa fa-exclamation-triangle"></i>
                                    
                                    {{ $t('backend.reactivate_member_inline_warning') }}
                                </p>
                                <span v-if="errors.estado" v-text="errors.estado[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.rol }">
                                <label for="rol">{{ $t('backend.role') }}</label>
                                <select
                                    class="form-control"
                                    v-model="form.rol"
                                >
                                    <option
                                        v-for="(label, key) in rolesFallback"
                                        :key="key"
                                        :value="key"
                                    >
                                        {{ label }}
                                    </option>
                                </select>
                                <span v-if="errors.rol" v-text="errors.rol[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.cargo }">
                                <label for="cargo">{{ $t('backend.cargo') }}</label>
                                <input v-model="form.cargo" name="cargo" type="text" class="form-control"
                                    required>
                                <span v-if="errors.cargo" v-text="errors.cargo[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div v-show="form.despliegue == 'Comunidad' && idEquipo"  class="col-md-4">
                                <div class="form-group">
                                    <label for="comunidades">{{ $t('backend.community') }}</label>
                                    <vue-tags-input
                                        v-model="tag"
                                        :tags="comunidades"
                                        add-only-from-autocomplete
                                        :autocompleteItems="filteredComunidadTags"
                                        placeholder=""
                                        @tags-changed="handleTagChange"
                                    />
                                    <p class="help-block">
                                    
                                    </p>
                                </div>
                            </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.fechaInicio }">
                                <label for="fechaInicio">{{ $t('backend.start_date') }}</label>
                                <input v-model="form.fechaInicio" name="fechaInicio" type="date" class="form-control"
                                    required>
                                <span v-if="errors.fechaInicio" v-text="errors.fechaInicio[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.proyeccion }">
                                <label for="proyeccion">{{ $t('backend.proyeccion') }}</label>
                                <input v-model="form.proyeccion" name="proyeccion" type="number" class="form-control">
                                <span v-if="errors.proyeccion" v-text="errors.proyeccion[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div v-show="form.estado == '0'" class="col-md-4">
                            <div :class="{ 'form-group': true, 'has-error': errors.fechaFin }">
                                <label for="fechaFin">{{ $t('backend.end_date') }}</label>
                                <input v-model="form.fechaFin" name="fechaFin" type="date" class="form-control" required>
                                <span v-if="errors.fechaFin" v-text="errors.fechaFin[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.archivo_carta_compromiso }">
                                <label for="archivo_carta_compromiso">{{ $t('backend.commitment_letter') }}</label>
                                <a v-if="form.archivo_carta_compromiso != null" :href="'/'+form.archivo_carta_compromiso" target="_blank"> {{ $t('backend.view_uploaded_letter') }}</a>
                                <input type="file" hidden  style="display: none;" ref="archivo_carta_compromiso"  @change="guardarCartaCompromiso">

                                <button  class="btn btn-light" @click="selectCarta" ><i class="fa fa-edit"></i></button>
                                {{ this.nombre_carta_compromiso }}
                                <span v-if="errors.archivo_carta_compromiso" v-text="errors.archivo_carta_compromiso[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.descripcion_rol }">
                                <label for="descripcion_rol">{{ $t('backend.role_description') }}</label>
                                <input v-model="form.descripcion_rol" name="descripcion_rol" type="text" class="form-control"
                                    required>
                                <span v-if="errors.descripcion_rol" v-text="errors.descripcion_rol[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.meta }">
                                <label for="meta">{{ $t('backend.goal') }}</label>
                                <small class="pull-right" :class="contadorClase(form.meta)">{{ largo(form.meta) }}/{{ maxMetaHitos }}</small>
                                <textarea v-model="form.meta" name="meta" class="form-control" rows="3" :maxlength="maxMetaHitos" required></textarea>
                                <span v-if="errors.meta" v-text="errors.meta[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.hitos }">
                                <label for="hitos">{{ $t('backend.milestones') }}</label>
                                <small class="pull-right" :class="contadorClase(form.hitos)">{{ largo(form.hitos) }}/{{ maxMetaHitos }}</small>
                                <textarea v-model="form.hitos" name="hitos" class="form-control" rows="3" :maxlength="maxMetaHitos" required></textarea>
                                <span v-if="errors.hitos" v-text="errors.hitos[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.dia_hora_reunion }">
                                <label for="dia_hora_reunion">{{ $t('backend.meeting_day_and_time') }}</label>
                                <input v-model="form.dia_hora_reunion" name="dia_hora_reunion" type="text" class="form-control"
                                    required>
                                <span v-if="errors.dia_hora_reunion" v-text="errors.dia_hora_reunion[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.periodicidad_reunion }">
                                <label for="periodicidad_reunion">{{ $t('backend.meeting_frequency') }}</label>
                                <input v-model="form.periodicidad_reunion" name="periodicidad_reunion" type="text" class="form-control" required>
                                <span v-if="errors.periodicidad_reunion" v-text="errors.periodicidad_reunion[0]" class="help-block"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.impacto }">
                                <label for="impacto">{{ $t('backend.impact') }}</label>
                                <input v-model="form.impacto" name="impacto" type="text" class="form-control"
                                    required>
                                <span v-if="errors.impacto" v-text="errors.impacto[0]" class="help-block"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{ 'form-group': true, 'has-error': errors.capacidades }">
                                <label for="capacidades">{{ $t('backend.skills') }}</label>
                                <input v-model="form.capacidades" name="capacidades" type="text" class="form-control" required>
                                <span v-if="errors.capacidades" v-text="errors.capacidades[0]" class="help-block"></span>
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
                    <div v-if="errorMsg" class="row">
                        <p class="text-center bg-danger">
                            {{ errorMsg }}
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

import vSelect from 'vue-select';
import Simplert from 'vue2-simplert';
import VueTagsInput from '@johmun/vue-tags-input';

export default {
    components: { 'v-select': vSelect , VueTagsInput},
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
                idIntegrante: null,
                idPersona: null,
                rol: null,
                cargo: null,
                despliegue: null,
                relacion: null,
                estado: 1,
                fechaInicio: null,
                fechaFin: null,
                proyeccion: null,
                archivo_carta_compromiso: '',
                descripcion_rol : null,
                meta : null,
                hitos : null,
                dia_hora_reunion : null,
                periodicidad_reunion : null,
                impacto : null,
                capacidades : null,
                idComunidad: null,
            },
            comunidad: '',
            comunidades: [],
            autocompleteComunidadesTags: [],
            tags: [],
            tag: '',
            errors: {},
            guardado: false,
            errorMsg: '',
            estadoOriginal: null,
            maxMetaHitos: 500,
        }
    },
    mounted() {
        Event.$on('integrante:crear', this.nuevo);
        Event.$on('integrante:editar', this.editar);
        this.guardado = false;
        this.getComunidades();

        // El cierre nativo del modal (tecla ESC o clic en el backdrop) no pasa
        // por cancelar(), así que limpiamos el form también acá para que el
        // próximo "crear/editar" no arrastre datos del integrante anterior.
        $('#inscribir-modal').on('hidden.bs.modal', () => {
            this.reset();
            this.reset_errors();
        });
    },
    watch: {
        persona(v, vv) {
            if (v) this.form.idPersona = v.idPersona
        },
        'form.estado'(nuevo, viejo) {
            // De INACTIVO → ACTIVO
            if (viejo == 0 && nuevo == 1) {
                this.form.fechaInicio = null;
                this.form.fechaFin = null;
            }

            // De ACTIVO → INACTIVO
            if (nuevo == 0) {
                // no borramos fechaInicio
                if (!this.form.fechaFin) {
                    this.errors.fechaFin = ['La fecha de fin es obligatoria'];
                }
            }
        },
        rolesAplicado(newVal) {
            if (newVal.length > 0) {
                this.rolSeleccionado = newVal[0].text;
            }
        }

    },

    computed: {
        editando() {
            if (this.form['idIntegrante'])
                return true
            return false
        },
        filteredComunidadTags() {
            return this.autocompleteComunidadesTags.filter(i => {
                return i.text.toLowerCase().indexOf(this.tag.toLowerCase()) !== -1;
            });
        },
        rolesFallback() {
            return this.$i18n.messages[this.$i18n.locale].backend.roles_integrantes;
        },
        estadoEraInactivo() {
            return this.estadoOriginal == 0;
        }
    },
    methods: {
        guardar() {
            if (
                this.editando &&
                this.estadoOriginal == 0 &&
                this.form.estado == 1
            ) {
                this.confirmarReactivacion();
                return;
            }

            if (this.form['idIntegrante'])
                this.update();
            else
                this.store();
        },
        store() {
            if (this.comunidades.length > 0){
                this.form.idComunidad = this.comunidades[0].idComunidad;
            } else if (this.idEquipo){
                this.form.idComunidad = null;
            }
            this.errorMsg = '';
            axios.post('/admin/ajax/equipos/' + this.idEquipo + '/integrante/crear', this.form)
                .then((datos) => {
                    this.form.idIntegrante = datos.data.idIntegrante;
                    this.submitFiles();
                    this.guardado = true;
                    setTimeout(() => {
                        this.guardado = false;
                        this.$emit('actualizar');
                        this.cancelar();
                    }, 2000);
                })
                .catch((error) => { this.handleSaveError(error); });

        },

        update() {
            if (this.comunidades.length > 0){
                this.form.idComunidad = this.comunidades[0].idComunidad;
            } else if (this.idEquipo){
                this.form.idComunidad = null;
            }
            this.errorMsg = '';
            axios.put('/admin/ajax/equipos/' + this.form.idEquipo + '/integrante/' + this.form.idIntegrante, this.form)
                .then((datos) => {
                    if (this.archivo_carta_compromiso)
                        this.submitFiles();
                    this.guardado = true;
                    setTimeout(() => {
                        this.guardado = false;
                        this.$emit('actualizar');
                        this.cancelar();
                    }, 2000);
                })
                .catch((error) => { this.handleSaveError(error); });
        },
        handleSaveError(error) {
            const data = error.response && error.response.data;
            if (data && data.errors) {
                // 422: errores de validación por campo (ej: meta/hitos > 500).
                this.errors = data.errors;
                this.errorMsg = '';
            } else {
                // 500 o error de red: no hay errores por campo. Avisamos al
                // usuario en vez de dejar el modal sin feedback (antes quedaba
                // errors = undefined y el guardado parecía silencioso).
                this.errors = {};
                this.errorMsg = this.$t('backend.save_generic_error');
            }
        },
        confirmarReactivacion() {
            this.$refs.confirmar.openSimplert({
                title: this.$t('backend.reactivate_member_title'),
                message: `
                    ${this.$t('backend.reactivate_member_message_1')}
                    <br><br>
                    ${this.$t('backend.reactivate_member_message_2')}
                `,
                useConfirmBtn: true,
                isShown: true,
                customConfirmBtnText: this.$t('backend.reactivate_member_confirm'),
                customConfirmBtnClass: 'btn btn-warning',
                customCloseBtnText: this.$t('backend.cancel'),
                onConfirm: () => {
                    this.update();
                }
            });
        },

        getComunidades(){
            axios.get('/ajax/comunidades/equipo/'+ this.idEquipo )
                .then((datos) => {
                    this.autocompleteComunidadesTags = this.formatComunidades(datos.data);
                }).catch((error) => { console.error('No se pudieron cargar las comunidades', error); });
        },
        formatComunidades(response) {
            return response.map(comunidad => ({
                text: comunidad.nombre,
                idComunidad: comunidad.idComunidad
            }));
        },
        handleTagChange(newTags) {
            if (newTags.length > 0) {
                    this.comunidades = [newTags[0]];
                } else {
                    this.comunidades = [];
                }
        },

        selectCarta: function () {
            this.$refs.archivo_carta_compromiso.click();
        },

        guardarCartaCompromiso: function () {
            this.archivo_carta_compromiso = this.$refs.archivo_carta_compromiso.files[0];
            this.nombre_carta_compromiso = this.$refs.archivo_carta_compromiso.files[0].name;
        },
        submitFiles() {
            if(this.archivo_carta_compromiso || this.archivo_plan_de_trabajo){
                const formData = new FormData();
                formData.append('archivo_carta_compromiso', this.archivo_carta_compromiso);
                formData.append('archivo_plan_de_trabajo', this.archivo_plan_de_trabajo);
                const headers = { 'Content-Type': 'multipart/form-data' };
                axios.post('/admin/ajax/equipos/' + this.form.idEquipo + '/integrante/' + this.form['idIntegrante'] + '/archivos', formData, { headers }).then(response => {
                    this.form.archivo_carta_compromiso = response.data.archivo_carta_compromiso;
                //   console.log(response);
                    this.archivo_carta_compromiso = null;
                    this.nombre_carta_compromiso = '';

                }).catch((error) => {
                });
            }
        },
        eliminar() {
            axios.delete('/admin/ajax/equipos/' + this.form.idEquipo + '/integrante/' + this.form.idIntegrante, this.form)
                .then((datos) => {
                    Event.$emit('integrantes:refrescar');
                    location.reload();
                    this.cancelar();
                })
                .catch((error) => { this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            // Limpiamos antes del fetch para no mostrar datos del integrante
            // anterior mientras llega la respuesta (el modal se abre sincrónico).
            this.reset();
            this.reset_errors();
            this.show();
            axios.get('/admin/ajax/equipos/' + this.idEquipo + '/integrante/' + p.idIntegrante)
                .then((datos) => {
                    this.form = datos.data;
                    this.estadoOriginal = datos.data.estado;
                    this.form.fechaInicio = moment(this.form.fechaInicio).format('YYYY-MM-DD');
                    if (this.form.fechaFin)
                        this.form.fechaFin = moment(this.form.fechaFin).format('YYYY-MM-DD');
                    this.persona = datos.data.persona;
                    this.persona.nombre = datos.data.personaData.nombre;

                    if (this.form.idComunidad) {
                        const comunidadSeleccionada = this.autocompleteComunidadesTags.find(
                            c => c.idComunidad === this.form.idComunidad
                        );

                        if (comunidadSeleccionada) {
                            this.comunidades = [{
                                ...comunidadSeleccionada,
                                tiClasses: ['ti-valid']
                            }];
                        }
                    } else {
                        this.comunidades = [];
                    }
                }).catch((error) => {
                    // Si el GET falla no dejamos el modal con datos a medias:
                    // avisamos y lo cerramos para que el usuario reintente.
                    console.error('No se pudo cargar el integrante', error);
                    this.errorMsg = this.$t('backend.save_generic_error');
                    this.hide();
                });
        },
        largo(valor) {
            return (valor || '').length;
        },
        contadorClase(valor) {
            const len = this.largo(valor);
            if (len >= this.maxMetaHitos) return 'text-danger';
            if (len >= this.maxMetaHitos - 50) return 'text-warning';
            return 'text-muted';
        },
        nuevo() {
            // Alta: limpiamos el form para no arrastrar el idIntegrante ni los
            // datos del integrante editado previamente (sino guardar() llamaría
            // a update() en vez de store() y pisaría otro registro).
            this.reset();
            this.reset_errors();
            this.show();
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
            this.form.idEquipo = this.idEquipo;
            this.persona = null;
            this.reset_errors();
        },
        reset_errors: function () {
            for (let field in this.errors) {
                this.errors[field] = null;
                delete this.errors[field];
            }
            this.errorMsg = '';
            this.guardado = false;
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
        onSearch: _.debounce(function (text, loading) {
            if (text.length > 3) {
                loading(true);
                axios.get('/ajax/coordinadores?coordinador=' + text)
                    .then((datos) => {
                        this.personas = datos.data.data;
                        loading(false);
                    })
                    .catch((error) => { loading(false); });
            }
        }, 400),

    }
}
</script>