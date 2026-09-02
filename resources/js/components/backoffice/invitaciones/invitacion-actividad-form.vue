<template>
    <div class="invitacion-actividad-component">
        <simplert ref="loading"></simplert>

        <!-- Confirmación de envío exitoso -->
        <div v-show="enviado" class="callout callout-success">
            <h4>Invitación enviada</h4>
            <p>Se despachó la invitación a {{ enviadoA }} persona(s). La entrega respeta
               a quienes se dieron de baja de este canal.</p>
        </div>

        <!-- Errores de validación -->
        <div v-show="tieneErrores" class="callout callout-danger">
            <h4>Revisá los siguientes datos:</h4>
            <ul>
                <li v-for="error in validationErrors">{{ error[0] }}</li>
            </ul>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Invitar a una actividad</h3>
            </div>
            <div class="box-body">

                <!-- Canal de envío -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Canal</label>
                            <div>
                                <label class="radio-inline">
                                    <input type="radio" value="push" v-model="canal" @change="onCanalChange"> Push (app)
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" value="email" v-model="canal" @change="onCanalChange"> Email
                                </label>
                                <label class="radio-inline text-muted">
                                    <input type="radio" disabled> WhatsApp <small>(próximamente)</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aviso de privacidad -->
                <div class="callout callout-info">
                    <p style="margin-bottom:0" v-if="canal === 'push'">
                        Esta invitación se envía <strong>dentro de la app</strong> (push) a quienes
                        aceptaron recibir notificaciones. No exporta ni comparte datos de contacto.
                    </p>
                    <p style="margin-bottom:0" v-else>
                        Esta invitación se envía por <strong>email</strong> a quienes aceptaron recibir
                        correos. No exporta ni comparte datos de contacto.
                    </p>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Países destino</label>
                            <v-select
                                    multiple
                                    :options="paises"
                                    label="nombre"
                                    placeholder="Seleccioná uno o más países"
                                    v-model="paisesSeleccionados"
                                    @input="onPaisesChange"
                            >
                                <span slot="no-options"></span>
                            </v-select>
                            <p class="help-block">Podés elegir varios (ej. Venezuela y Colombia).</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>A quién</label>
                            <select class="form-control" v-model="segmento" @change="resetPreview">
                                <option value="coordinadores">Coordinadores (rol)</option>
                                <option value="coordinadores_gestion">Coordinadores de actividad / equipo / comunidad</option>
                                <option value="activos">Voluntarios activos (últimos 90 días)</option>
                                <option value="frecuentes">Voluntarios frecuentes (3+ participaciones)</option>
                                <option value="jefes_cuadrilla">Jefes de cuadrilla</option>
                                <option value="jefaturas">Jefaturas / liderazgos</option>
                                <option value="todos">Todos los voluntarios</option>
                            </select>
                            <p class="help-block">{{ ayudaSegmento }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Actividad a la que invitás</label>
                            <v-select
                                    :options="actividades"
                                    label="nombreActividad"
                                    placeholder="Elegí la actividad"
                                    v-model="actividadSeleccionada"
                                    :disabled="paisesSeleccionados.length === 0"
                            >
                                <span slot="no-options">
                                    {{ paisesSeleccionados.length === 0
                                        ? 'Elegí primero un país' : 'Sin actividades en los países elegidos' }}
                                </span>
                            </v-select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>{{ canal === 'email' ? 'Asunto' : 'Título' }}</label>
                            <input type="text"
                                   class="form-control"
                                   :maxlength="maxTitulo"
                                   v-model="titulo"
                                   @input="resetPreview"
                                   placeholder="Ej.: Sumate a la respuesta a la emergencia">
                            <p class="help-block">{{ titulo.length }}/{{ maxTitulo }}</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Mensaje</label>
                            <textarea class="form-control"
                                      :rows="canal === 'email' ? 6 : 3"
                                      :maxlength="maxMensaje"
                                      v-model="mensaje"
                                      @input="resetPreview"
                                      placeholder="Contales de qué se trata y cómo pueden ayudar."></textarea>
                            <p class="help-block">{{ mensaje.length }}/{{ maxMensaje }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-footer">
                <button class="btn btn-default"
                        :disabled="!puedePrevisualizar"
                        @click="previsualizar">
                    <i class="fa fa-users"></i> Ver a cuántos llega
                </button>

                <transition name="fade">
                    <div v-if="destinatarios !== null" class="callout"
                         :class="destinatarios > 0 ? 'callout-warning' : 'callout-default'"
                         style="margin-top:15px">
                        <template v-if="destinatarios > 0">
                            <h4>Esta invitación va a llegar a {{ destinatarios }} persona(s)</h4>
                            <p>Revisá el título y el mensaje. Al confirmar, se despacha el envío.</p>
                            <button class="btn btn-primary" @click="enviar">
                                <i class="fa fa-paper-plane"></i> Confirmar y enviar
                            </button>
                        </template>
                        <template v-else>
                            <h4>No hay destinatarios con este criterio</h4>
                            <p>Nadie en los países/segmento elegidos puede recibir por este canal. Ajustá la selección.</p>
                        </template>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "invitacion-actividad-form",
        data() {
            return {
                paises: [],
                actividades: [],
                paisesSeleccionados: [],
                actividadSeleccionada: null,
                segmento: 'coordinadores',
                canal: 'push',
                titulo: '',
                mensaje: '',
                destinatarios: null,   // null = todavía no previsualizó
                enviado: false,
                enviadoA: 0,
                validationErrors: {},
            }
        },
        created() {
            this.getPaises();
        },
        computed: {
            tieneErrores() {
                return this.validationErrors.length > 0;
            },
            ayudaSegmento() {
                const ayudas = {
                    coordinadores: 'Personas con el rol global "coordinador" en la plataforma.',
                    coordinadores_gestion: 'Quienes coordinan al menos una actividad, equipo o comunidad (membresía real, distinta del rol global).',
                    activos: 'Voluntarios que se inscribieron a alguna actividad en los últimos 90 días.',
                    frecuentes: 'Voluntarios con 3 o más participaciones con asistencia confirmada.',
                    jefes_cuadrilla: 'Voluntarios que tuvieron el rol de jefe de cuadrilla en alguna actividad.',
                    jefaturas: 'Voluntarios que tuvieron alguna jefatura o liderazgo (cuadrilla, escuela o trabajo) en alguna actividad.',
                    todos: 'Todos los voluntarios de los países elegidos.',
                };
                return ayudas[this.segmento] || '';
            },
            idsPaises() {
                return this.paisesSeleccionados.map(p => p.id);
            },
            // Límites por canal: push es corto por la plataforma; email admite más.
            maxTitulo() {
                return this.canal === 'email' ? 150 : 65;
            },
            maxMensaje() {
                return this.canal === 'email' ? 2000 : 240;
            },
            puedePrevisualizar() {
                return this.idsPaises.length > 0 && this.segmento;
            },
            listoParaEnviar() {
                return this.idsPaises.length > 0
                    && this.actividadSeleccionada
                    && this.titulo.trim().length > 0
                    && this.mensaje.trim().length > 0;
            },
        },
        methods: {
            getPaises() {
                axios.get('/admin/ajax/comunicaciones/invitaciones/paises')
                    .then((r) => { this.paises = r.data; })
                    .catch(() => {});
            },
            onPaisesChange() {
                this.resetPreview();
                this.actividadSeleccionada = null;
                this.actividades = [];
                if (this.idsPaises.length === 0) return;

                axios.get('/admin/ajax/comunicaciones/invitaciones/actividades', {
                    params: { idsPaises: this.idsPaises }
                })
                    .then((r) => { this.actividades = r.data; })
                    .catch(() => {});
            },
            resetPreview() {
                this.destinatarios = null;
                this.enviado = false;
            },
            onCanalChange() {
                // El conteo depende del canal (distinto opt-in), así que se re-previsualiza.
                this.resetPreview();
            },
            mostrarLoading() {
                this.$refs.loading.openSimplert({
                    title: 'Espera...',
                    message: "<i class=\"fa fa-spinner fa-spin fa-4x\"></i>",
                    hideAllButton: true,
                    isShown: true,
                    disableOverlayClick: true,
                    type: ''
                });
            },
            ocultarLoading() {
                this.$refs.loading.justCloseSimplert();
            },
            previsualizar() {
                this.validationErrors = [];
                this.mostrarLoading();

                axios.post('/admin/ajax/comunicaciones/invitaciones/preview', {
                    idsPaises: this.idsPaises,
                    segmento: this.segmento,
                    canal: this.canal,
                })
                    .then((r) => {
                        this.destinatarios = r.data.destinatarios;
                        this.ocultarLoading();
                    })
                    .catch((error) => this.manejarError(error));
            },
            enviar() {
                if (!this.listoParaEnviar) {
                    this.validationErrors = [['Completá país, actividad, título y mensaje antes de enviar.']];
                    return;
                }
                this.validationErrors = [];
                this.mostrarLoading();

                axios.post('/admin/ajax/comunicaciones/invitaciones/enviar', {
                    idActividad: this.actividadSeleccionada.idActividad,
                    idsPaises: this.idsPaises,
                    segmento: this.segmento,
                    canal: this.canal,
                    titulo: this.titulo,
                    mensaje: this.mensaje,
                })
                    .then((r) => {
                        this.ocultarLoading();
                        this.enviado = true;
                        this.enviadoA = r.data.destinatarios;
                        this.destinatarios = null;
                    })
                    .catch((error) => this.manejarError(error));
            },
            manejarError(error) {
                this.ocultarLoading();
                if (error.response && error.response.status === 422) {
                    this.validationErrors = Object.values(error.response.data.errors);
                } else {
                    this.validationErrors = [['Ocurrió un error al procesar la solicitud. Intentá de nuevo.']];
                }
            },
        }
    }
</script>

<style scoped>
    .fade-enter-active, .fade-leave-active {
        transition: opacity .2s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>
