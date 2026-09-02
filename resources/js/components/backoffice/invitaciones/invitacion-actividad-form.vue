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
                <h3 class="box-title">Enviar una comunicación</h3>
            </div>
            <div class="box-body">

                <!-- Objetivo de la comunicación (selección visual) -->
                <div class="form-group">
                    <label>Objetivo</label>
                    <div class="canal-cards">
                        <div class="canal-card"
                             :class="{ 'is-selected': objetivo === 'actividad' }"
                             @click="seleccionarObjetivo('actividad')">
                            <i class="fa fa-calendar-check-o canal-card__icon"></i>
                            <div class="canal-card__titulo">Actividad</div>
                            <div class="canal-card__desc">Invitar a una actividad</div>
                        </div>

                        <div class="canal-card"
                             :class="{ 'is-selected': objetivo === 'campania' }"
                             @click="seleccionarObjetivo('campania')">
                            <i class="fa fa-bullhorn canal-card__icon"></i>
                            <div class="canal-card__titulo">Campaña</div>
                            <div class="canal-card__desc">Difundir o captar una campaña</div>
                        </div>
                    </div>
                </div>

                <!-- Canal de envío (solo para actividad; campaña va por email) -->
                <div class="form-group" v-if="objetivo === 'actividad'">
                    <label>Canal</label>
                    <div class="canal-cards">
                        <div class="canal-card"
                             :class="{ 'is-selected': canal === 'push' }"
                             @click="seleccionarCanal('push')">
                            <i class="fa fa-bell canal-card__icon"></i>
                            <div class="canal-card__titulo">Push</div>
                            <div class="canal-card__desc">Notificación en la app</div>
                        </div>

                        <div class="canal-card"
                             :class="{ 'is-selected': canal === 'email' }"
                             @click="seleccionarCanal('email')">
                            <i class="fa fa-envelope canal-card__icon"></i>
                            <div class="canal-card__titulo">Email</div>
                            <div class="canal-card__desc">Correo con formato e imágenes</div>
                        </div>

                        <div class="canal-card is-disabled">
                            <span class="canal-card__badge">Próximamente</span>
                            <i class="fa fa-whatsapp canal-card__icon"></i>
                            <div class="canal-card__titulo">WhatsApp</div>
                            <div class="canal-card__desc">Mensaje directo</div>
                        </div>
                    </div>
                </div>

                <!-- Aviso de privacidad -->
                <div class="callout callout-info">
                    <p style="margin-bottom:0" v-if="objetivo === 'campania'">
                        La comunicación se envía por <strong>email</strong>
                        {{ audiencia === 'suscriptos' ? 'a los suscriptos de la campaña' : 'a los voluntarios del segmento' }},
                        con un enlace a la campaña. No exporta ni comparte datos de contacto.
                    </p>
                    <p style="margin-bottom:0" v-else-if="canal === 'push'">
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
                            <label>{{ multiPais ? 'Países destino' : 'País destino' }}</label>
                            <v-select
                                    multiple
                                    :options="paises"
                                    label="nombre"
                                    :placeholder="multiPais ? 'Seleccioná uno o más países' : 'Seleccioná el país'"
                                    v-model="paisesSeleccionados"
                                    @input="onPaisesChange"
                            >
                                <span slot="no-options"></span>
                            </v-select>
                            <p class="help-block">{{ ayudaPaises }}</p>
                        </div>
                    </div>

                    <div class="col-md-6" v-if="mostrarSegmento">
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

                <!-- Objetivo actividad: elegir la actividad -->
                <div class="row" v-if="objetivo === 'actividad'">
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

                <!-- Objetivo campaña: elegir la campaña y la audiencia -->
                <div class="row" v-if="objetivo === 'campania'">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Campaña</label>
                            <v-select
                                    :options="campanas"
                                    label="nombre"
                                    placeholder="Elegí la campaña"
                                    v-model="campaniaSeleccionada"
                                    :disabled="paisesSeleccionados.length === 0"
                                    @input="resetPreview"
                            >
                                <span slot="no-options">
                                    {{ paisesSeleccionados.length === 0
                                        ? 'Elegí primero un país' : 'Sin campañas en los países elegidos' }}
                                </span>
                            </v-select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Audiencia</label>
                            <select class="form-control" v-model="audiencia" @change="onAudienciaChange">
                                <option value="suscriptos">Suscriptos de la campaña</option>
                                <option value="segmento">Segmento de voluntarios</option>
                            </select>
                            <p class="help-block">{{ ayudaAudiencia }}</p>
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

                            <!-- Push: texto plano y corto (límite de plataforma). -->
                            <template v-if="canal === 'push'">
                                <textarea class="form-control"
                                          rows="3"
                                          :maxlength="maxMensaje"
                                          v-model="mensaje"
                                          @input="resetPreview"
                                          placeholder="Contales de qué se trata y cómo pueden ayudar."></textarea>
                                <p class="help-block">{{ mensaje.length }}/{{ maxMensaje }} · Texto corto, sin formato (es una notificación).</p>
                            </template>

                            <!-- Email: texto enriquecido con formato e imágenes. -->
                            <template v-else>
                                <tinymce-editor
                                        v-model="mensaje"
                                        :init="{
                                            menubar: false,
                                            file_picker_callback: tiny_mce_filemanager_callback,
                                            relative_urls: false,
                                            resize: true,
                                            height: 320,
                                            branding: false,
                                        }"
                                        toolbar="undo redo | styleselect | bold italic | forecolor | alignleft aligncenter alignright | bullist numlist | link image | removeformat"
                                        plugins="paste autoresize image preview link lists"
                                ></tinymce-editor>
                                <p class="help-block">Podés dar formato, agregar enlaces e imágenes. El correo se envía con el logo de TECHO y un botón a la actividad.</p>
                            </template>
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
                            <h4>Llega a {{ destinatarios }} de {{ totalSegmento }} persona(s)</h4>
                            <p v-if="sinCanal > 0">
                                {{ sinCanal }} {{ sinCanal === 1 ? 'persona no puede' : 'personas no pueden' }}
                                recibir ({{ motivoSinCanal }}).
                                <template v-if="canal === 'push' && objetivo === 'actividad'"> Con <strong>Email</strong> quizás llegues a más.</template>
                            </p>
                            <p>Revisá el {{ canal === 'email' ? 'asunto' : 'título' }} y el mensaje. Al confirmar, se despacha el envío.</p>
                            <button class="btn btn-primary" @click="enviar">
                                <i class="fa fa-paper-plane"></i> Confirmar y enviar
                            </button>
                        </template>
                        <template v-else>
                            <h4>Nadie puede recibir con este criterio</h4>
                            <p v-if="totalSegmento > 0">
                                Hay {{ totalSegmento }} persona(s), pero ninguna puede recibir ({{ motivoSinCanal }}).
                                <template v-if="canal === 'push' && objetivo === 'actividad'"> Probá con <strong>Email</strong>.</template>
                            </p>
                            <p v-else>No hay personas para el criterio elegido. Ajustá la selección.</p>
                        </template>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<script>
    import editor from '@tinymce/tinymce-vue'
    import 'tinymce/tinymce'
    import 'tinymce/themes/silver/theme'
    import 'tinymce/plugins/paste'
    import 'tinymce/plugins/autoresize'
    import 'tinymce/plugins/image'
    import 'tinymce/plugins/preview'
    import 'tinymce/plugins/link'
    import 'tinymce/plugins/lists'

    export default {
        name: "invitacion-actividad-form",
        components: { 'tinymce-editor': editor },
        data() {
            return {
                paises: [],
                actividades: [],
                campanas: [],
                paisesSeleccionados: [],
                actividadSeleccionada: null,
                campaniaSeleccionada: null,
                objetivo: 'actividad',   // 'actividad' | 'campania'
                audiencia: 'suscriptos', // solo campaña: 'suscriptos' | 'segmento'
                segmento: 'coordinadores',
                canal: 'push',
                titulo: '',
                mensaje: '',
                destinatarios: null,   // null = todavía no previsualizó (alcanzables por el canal)
                totalSegmento: null,   // tamaño total del segmento (ignora el opt-in)
                sinCanal: 0,           // cuántos no pueden recibir por el canal elegido
                enviado: false,
                enviadoA: 0,
                validationErrors: {},
            }
        },
        created() {
            this.getPaises();
        },
        watch: {
            // El editor de email (TinyMCE) actualiza `mensaje` por v-model, sin @input:
            // cualquier cambio del mensaje invalida el preview previo.
            mensaje() {
                this.resetPreview();
            },
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
            // El usuario puede alcanzar más de un país (admin multi-país / global).
            // Si su alcance es un solo país, la pantalla se comporta en modo mono-país.
            multiPais() {
                return this.paises.length > 1;
            },
            ayudaPaises() {
                if (this.paises.length === 0) return '';
                if (this.paises.length === 1) {
                    return 'Tu alcance es ' + this.paises[0].nombre + '. Solo podés enviar a ese país.';
                }
                return 'Podés elegir uno o varios países (tu alcance permite más de uno).';
            },
            canalLabel() {
                return this.canal === 'email' ? 'email' : 'push';
            },
            // El segmento de voluntarios aplica para actividad, y para campaña solo si la
            // audiencia elegida es "segmento" (no cuando son los suscriptos de la campaña).
            mostrarSegmento() {
                return this.objetivo === 'actividad'
                    || (this.objetivo === 'campania' && this.audiencia === 'segmento');
            },
            ayudaAudiencia() {
                return this.audiencia === 'suscriptos'
                    ? 'Los leads que se anotaron en la campaña (por email).'
                    : 'Voluntarios del segmento elegido, con un enlace a la campaña.';
            },
            // Motivo por el que parte de la audiencia no es alcanzable (para el preview).
            motivoSinCanal() {
                if (this.objetivo === 'campania' && this.audiencia === 'suscriptos') {
                    return 'no tienen email cargado';
                }
                return this.canal === 'push'
                    ? 'tienen las notificaciones apagadas o no registraron un dispositivo'
                    : 'no tienen email o se dieron de baja de los correos';
            },
            // Límites por canal: push es corto por la plataforma; email admite más.
            maxTitulo() {
                return this.canal === 'email' ? 150 : 65;
            },
            maxMensaje() {
                // Email usa editor HTML (sin maxlength en el textarea); este tope solo
                // aplica al textarea de push. Se mantiene alineado con el validador server.
                return this.canal === 'email' ? 20000 : 240;
            },
            puedePrevisualizar() {
                if (this.idsPaises.length === 0) return false;
                if (this.objetivo === 'campania') {
                    // La campaña define el objetivo (y, si es por suscriptos, el conteo).
                    return !!this.campaniaSeleccionada
                        && (this.audiencia === 'suscriptos' || !!this.segmento);
                }
                return !!this.segmento;
            },
            listoParaEnviar() {
                if (this.idsPaises.length === 0) return false;
                if (this.titulo.trim().length === 0 || this.mensaje.trim().length === 0) return false;
                if (this.objetivo === 'campania') {
                    return !!this.campaniaSeleccionada
                        && (this.audiencia === 'suscriptos' || !!this.segmento);
                }
                return !!this.actividadSeleccionada;
            },
        },
        methods: {
            getPaises() {
                axios.get('/admin/ajax/comunicaciones/invitaciones/paises')
                    .then((r) => {
                        this.paises = r.data;
                        // Alcance de un solo país: se preselecciona (no hay nada que elegir).
                        if (this.paises.length === 1) {
                            this.paisesSeleccionados = [this.paises[0]];
                            this.onPaisesChange();
                        }
                    })
                    .catch(() => {});
            },
            onPaisesChange() {
                this.resetPreview();
                this.actividadSeleccionada = null;
                this.campaniaSeleccionada = null;
                this.actividades = [];
                this.campanas = [];
                if (this.idsPaises.length === 0) return;

                this.cargarActividades();
                this.cargarCampanas();
            },
            cargarActividades() {
                axios.get('/admin/ajax/comunicaciones/invitaciones/actividades', {
                    params: { idsPaises: this.idsPaises }
                })
                    .then((r) => { this.actividades = r.data; })
                    .catch(() => {});
            },
            cargarCampanas() {
                axios.get('/admin/ajax/comunicaciones/invitaciones/campanas', {
                    params: { idsPaises: this.idsPaises }
                })
                    .then((r) => { this.campanas = r.data; })
                    .catch(() => {});
            },
            seleccionarObjetivo(objetivo) {
                if (this.objetivo === objetivo) return;
                this.objetivo = objetivo;
                // Campaña va solo por email (leads sin dispositivo + app sin deep link).
                if (objetivo === 'campania') {
                    this.canal = 'email';
                }
                this.resetPreview();
            },
            onAudienciaChange() {
                this.resetPreview();
            },
            resetPreview() {
                this.destinatarios = null;
                this.totalSegmento = null;
                this.sinCanal = 0;
                this.enviado = false;
            },
            seleccionarCanal(canal) {
                if (this.canal === canal) return;
                this.canal = canal;
                // El conteo depende del canal (distinto opt-in) y el formato del mensaje
                // cambia (push texto plano ↔ email HTML), así que se limpia el preview.
                this.resetPreview();
            },
            tiny_mce_filemanager_callback(callback, value, meta) {
                // Reusa el laravel-filemanager del backoffice (mismo patrón que actividad.vue):
                // las imágenes quedan hosteadas y se referencian por URL en el email.
                let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                let y = window.innerHeight || document.documentElement.clientHeight || document.getElementsByTagName('body')[0].clientHeight;
                let cmsURL = '/laravel-filemanager?editor=tinymce5&field_name=' + value;
                if (meta.filetype == 'image') { cmsURL = cmsURL + "&type=Images"; }
                else { cmsURL = cmsURL + "&type=Files"; }

                tinyMCE.activeEditor.windowManager.openUrl({
                    url: cmsURL,
                    title: 'Administrador de archivos',
                    width: x * 0.8,
                    height: y * 0.8,
                    resizable: "yes",
                    close_previous: "no",
                    onMessage: (api, message) => {
                        callback(message.content);
                    }
                });
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
            // Campos del objetivo comunes a preview y envío (según actividad/campaña).
            datosObjetivo() {
                const d = { objetivo: this.objetivo, idsPaises: this.idsPaises };
                if (this.objetivo === 'campania') {
                    d.idCampania = this.campaniaSeleccionada ? this.campaniaSeleccionada.id : null;
                    d.audiencia = this.audiencia;
                    if (this.audiencia === 'segmento') d.segmento = this.segmento;
                } else {
                    d.canal = this.canal;
                    d.segmento = this.segmento;
                }
                return d;
            },
            previsualizar() {
                this.validationErrors = [];
                this.mostrarLoading();

                axios.post('/admin/ajax/comunicaciones/invitaciones/preview', this.datosObjetivo())
                    .then((r) => {
                        this.destinatarios = r.data.destinatarios;
                        this.totalSegmento = r.data.total;
                        this.sinCanal = r.data.sin_canal;
                        this.ocultarLoading();
                    })
                    .catch((error) => this.manejarError(error));
            },
            enviar() {
                if (!this.listoParaEnviar) {
                    this.validationErrors = [['Completá los datos requeridos (objetivo, título y mensaje) antes de enviar.']];
                    return;
                }
                this.validationErrors = [];
                this.mostrarLoading();

                const datos = this.datosObjetivo();
                datos.titulo = this.titulo;
                datos.mensaje = this.mensaje;
                if (this.objetivo === 'actividad') {
                    datos.idActividad = this.actividadSeleccionada.idActividad;
                }

                axios.post('/admin/ajax/comunicaciones/invitaciones/enviar', datos)
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

    /* Selector de canal como cubos */
    .canal-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .canal-card {
        position: relative;
        width: 150px;
        padding: 16px 12px;
        text-align: center;
        border: 2px solid #d2d6de;
        border-radius: 6px;
        background: #fff;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s, transform .05s;
    }
    .canal-card:hover:not(.is-disabled) {
        border-color: #0092dd;
    }
    .canal-card.is-selected {
        border-color: #0092dd;
        box-shadow: 0 0 0 3px rgba(0, 146, 221, .15);
    }
    .canal-card.is-disabled {
        cursor: not-allowed;
        opacity: .55;
        background: #f7f7f7;
    }
    .canal-card__icon {
        font-size: 26px;
        color: #0092dd;
    }
    .canal-card.is-disabled .canal-card__icon {
        color: #999;
    }
    .canal-card__titulo {
        margin-top: 8px;
        font-weight: 700;
    }
    .canal-card__desc {
        font-size: 12px;
        color: #777;
        margin-top: 2px;
    }
    .canal-card__badge {
        position: absolute;
        top: -9px;
        right: -9px;
        background: #999;
        color: #fff;
        font-size: 10px;
        line-height: 1;
        padding: 3px 6px;
        border-radius: 10px;
        white-space: nowrap;
    }
</style>
