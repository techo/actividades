<template>
    <div>
        <!-- Modal -->
        <div class="modal fade" id="preguntas-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">{{ editando ? $t('backend.edit') : $t('backend.create') }} {{ $t('backend.pregunta') }}</h4>
                    </div>
                    <div class="modal-body">

                        <div v-if="errorGeneral" class="callout callout-danger">
                            <p>{{ errorGeneral }}</p>
                        </div>

                        <!-- Pregunta -->
                        <div :class="{ 'form-group': true, 'has-error': errors.pregunta }">
                            <label>{{ $t('backend.pregunta') }} <span class="text-danger">*</span></label>
                            <input v-model="form.pregunta" type="text" class="form-control" maxlength="500">
                            <span v-if="errors.pregunta" class="help-block">{{ errors.pregunta[0] }}</span>
                        </div>

                        <!-- Descripción (ayuda) -->
                        <div :class="{ 'form-group': true, 'has-error': errors.descripcion }">
                            <label>{{ $t('backend.descripcion_ayuda') }}</label>
                            <input v-model="form.descripcion" type="text" class="form-control" maxlength="1000">
                            <span v-if="errors.descripcion" class="help-block">{{ errors.descripcion[0] }}</span>
                        </div>

                        <!-- Tipo -->
                        <div :class="{ 'form-group': true, 'has-error': errors.tipo }">
                            <label>{{ $t('backend.tipo') }}</label>
                            <select v-model="form.tipo" class="form-control">
                                <option value="abierta">{{ $t('backend.tipo_abierta') }}</option>
                                <option value="desplegable">{{ $t('backend.tipo_desplegable') }}</option>
                            </select>
                            <span v-if="errors.tipo" class="help-block">{{ errors.tipo[0] }}</span>
                        </div>

                        <!-- Opciones (solo si tipo = desplegable) -->
                        <div v-if="form.tipo === 'desplegable'" :class="{ 'form-group': true, 'has-error': errors.opciones }">
                            <label>{{ $t('backend.opciones') }}</label>
                            <vue-tags-input
                                v-model="tagOpcion"
                                :tags="opcionesTags"
                                :add-on-key="[13, ',']"
                                :placeholder="$t('backend.opciones_placeholder')"
                                @tags-changed="newTags => opcionesTags = newTags"
                            />
                            <span class="help-block text-muted">{{ $t('backend.opciones_ayuda') }}</span>
                            <span v-if="errors.opciones" class="help-block">{{ errors.opciones[0] }}</span>
                        </div>

                        <!-- Requerida -->
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" v-model="form.requerida">
                                    {{ $t('backend.requerida') }}
                                </label>
                            </div>
                        </div>

                        <!-- Orden -->
                        <div :class="{ 'form-group': true, 'has-error': errors.orden }">
                            <label>{{ $t('backend.orden') }}</label>
                            <input v-model.number="form.orden" type="number" class="form-control" min="0">
                            <span v-if="errors.orden" class="help-block">{{ errors.orden[0] }}</span>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button class="btn" @click="cancelar()">{{ $t('backend.cancel') }}</button>
                        <button v-if="editando" class="btn btn-danger" @click.prevent="confirmarEliminar()">{{ $t('backend.eliminate') }}</button>
                        <button class="btn btn-primary" @click.prevent="guardar()" :disabled="guardando">
                            {{ guardando ? $t('backend.saving') : $t('backend.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="pull-right">
                            <button class="btn btn-primary" @click.prevent="abrirModalCrear()">
                                {{ $t('backend.create') }} <i class="fa fa-plus"></i>
                            </button>
                        </span>
                    </div>
                </div>
                <br>

                <div v-if="cargando" class="text-center">
                    <i class="fa fa-spinner fa-spin"></i> {{ $t('backend.loading') }}
                </div>

                <div v-else-if="preguntas.length === 0" class="text-muted text-center">
                    {{ $t('backend.no_preguntas') }}
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>{{ $t('backend.orden') }}</th>
                                <th>{{ $t('backend.pregunta') }}</th>
                                <th>{{ $t('backend.tipo') }}</th>
                                <th>{{ $t('backend.requerida') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pregunta in preguntas" :key="pregunta.id">
                                <td>{{ pregunta.orden }}</td>
                                <td>
                                    {{ pregunta.pregunta }}
                                    <small v-if="pregunta.descripcion" class="text-muted d-block">{{ pregunta.descripcion }}</small>
                                </td>
                                <td>{{ pregunta.tipo === 'desplegable' ? $t('backend.tipo_desplegable') : $t('backend.tipo_abierta') }}</td>
                                <td>
                                    <i :class="pregunta.requerida ? 'fa fa-check text-success' : 'fa fa-times text-muted'"></i>
                                </td>
                                <td>
                                    <button class="btn btn-xs btn-default" @click.prevent="abrirModalEditar(pregunta)">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import VueTagsInput from '@johmun/vue-tags-input';

export default {
    name: 'preguntas-actividad',
    components: { VueTagsInput },
    props: {
        actividadId: {
            type: Number,
            required: true,
        }
    },
    data() {
        return {
            preguntas: [],
            cargando: false,
            guardando: false,
            errorGeneral: null,
            errors: {},
            tagOpcion: '',
            opcionesTags: [],
            form: this.formVacio(),
        };
    },
    computed: {
        editando() {
            return !!this.form.id;
        },
    },
    mounted() {
        this.cargarPreguntas();
    },
    methods: {
        formVacio() {
            return {
                id: null,
                pregunta: '',
                descripcion: '',
                tipo: 'abierta',
                opciones: [],
                requerida: false,
                orden: 0,
            };
        },
        cargarPreguntas() {
            this.cargando = true;
            axios.get('/admin/ajax/actividades/' + this.actividadId + '/preguntas')
                .then((res) => { this.preguntas = res.data; })
                .catch(() => {})
                .finally(() => { this.cargando = false; });
        },
        abrirModalCrear() {
            this.form = this.formVacio();
            this.opcionesTags = [];
            this.tagOpcion = '';
            this.errors = {};
            this.errorGeneral = null;
            $('#preguntas-modal').modal('show');
        },
        abrirModalEditar(pregunta) {
            this.form = {
                id: pregunta.id,
                pregunta: pregunta.pregunta,
                descripcion: pregunta.descripcion || '',
                tipo: pregunta.tipo,
                opciones: pregunta.opciones || [],
                requerida: pregunta.requerida,
                orden: pregunta.orden,
            };
            this.opcionesTags = Array.isArray(pregunta.opciones)
                ? pregunta.opciones.map(o => ({ text: o }))
                : [];
            this.tagOpcion = '';
            this.errors = {};
            this.errorGeneral = null;
            $('#preguntas-modal').modal('show');
        },
        cancelar() {
            $('#preguntas-modal').modal('hide');
            this.form = this.formVacio();
            this.opcionesTags = [];
            this.tagOpcion = '';
            this.errors = {};
            this.errorGeneral = null;
        },
        guardar() {
            if (this.editando) {
                this.actualizar();
            } else {
                this.crear();
            }
        },
        payload() {
            return {
                pregunta:    this.form.pregunta,
                descripcion: this.form.descripcion || null,
                tipo:        this.form.tipo,
                opciones:    this.form.tipo === 'desplegable'
                                ? this.opcionesTags.map(t => t.text)
                                : [],
                requerida:   this.form.requerida,
                orden:       this.form.orden,
            };
        },
        crear() {
            this.guardando = true;
            this.errorGeneral = null;
            axios.post('/admin/ajax/actividades/' + this.actividadId + '/preguntas', this.payload())
                .then(() => {
                    this.cargarPreguntas();
                    this.cancelar();
                })
                .catch((error) => {
                    if (error.response && error.response.data && error.response.data.errors) {
                        this.errors = error.response.data.errors;
                    } else {
                        this.errorGeneral = this.$t('backend.error_guardando');
                    }
                })
                .finally(() => { this.guardando = false; });
        },
        actualizar() {
            this.guardando = true;
            this.errorGeneral = null;
            axios.put('/admin/ajax/actividades/' + this.actividadId + '/preguntas/' + this.form.id, this.payload())
                .then(() => {
                    this.cargarPreguntas();
                    this.cancelar();
                })
                .catch((error) => {
                    if (error.response && error.response.data && error.response.data.errors) {
                        this.errors = error.response.data.errors;
                    } else {
                        this.errorGeneral = this.$t('backend.error_guardando');
                    }
                })
                .finally(() => { this.guardando = false; });
        },
        confirmarEliminar() {
            if (!confirm(this.$t('backend.confirm_delete'))) return;
            this.eliminar();
        },
        eliminar() {
            axios.delete('/admin/ajax/actividades/' + this.actividadId + '/preguntas/' + this.form.id)
                .then(() => {
                    this.cargarPreguntas();
                    this.cancelar();
                })
                .catch(() => {
                    this.errorGeneral = this.$t('backend.error_guardando');
                });
        },
    },
};
</script>

<style scoped>
</style>
