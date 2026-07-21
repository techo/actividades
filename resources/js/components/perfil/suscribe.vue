<template>
    <div class="card">
        <div class="card-body">
            <h2 class="text-uppercase mb-4 text-center">{{ $t('suscribe.title') }}</h2>
            <p class="m-1 text-center">{{ $t('suscribe.subtitle') }}</p>

            <!-- REQ 4 — Ya inscripto -->
            <div v-if="yaInscripto" class="alert alert-warning text-center mt-4 p-4">
                <i class="fa fa-exclamation-circle"></i>
                {{ $t('suscribe.already_registered') }}
            </div>

            <!-- REQ 5 — Formulario + mensaje de agradecimiento -->
            <div v-if="!guardado && !yaInscripto">

                <!-- REQ 3 — Datos del usuario logueado (si aplica) -->
                <div v-if="estaLogueado" class="alert alert-info mt-3">
                    <i class="fa fa-user"></i>
                    {{ $t('suscribe.logged_in_as') }}: <strong>{{ suscriptor.nombre }} {{ suscriptor.apellido }}</strong> ({{ suscriptor.mail }})
                </div>

                <!-- Información Personal (oculta si está logueado) -->
                <div v-if="!estaLogueado">
                    <h4 class="mt-2">{{ $t('suscribe.personal_info') }}</h4>
                    <form @submit.prevent="guardar" :id="formId">
                        <div class="row">
                            <div class="col-md-4">
                                <input
                                    class="form-control"
                                    :placeholder="$t('suscribe.name')"
                                    v-model="suscriptor.nombre"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <input
                                    class="form-control"
                                    :placeholder="$t('suscribe.lastname')"
                                    v-model="suscriptor.apellido"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <input
                                    class="form-control"
                                    :placeholder="documentoLabel"
                                    v-model="suscriptor.dni"
                                    required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <!-- REQ 2 — Aviso de email existente -->
                                <input
                                    class="form-control"
                                    :placeholder="$t('suscribe.email')"
                                    v-model="suscriptor.mail"
                                    @blur="verificarEmail"
                                    required>
                                <small v-if="emailExiste" class="form-text text-warning">
                                    <i class="fa fa-exclamation-triangle"></i>
                                    {{ $t('suscribe.user_exists_warning') }}
                                </small>
                            </div>

                            <div class="col-md-4">
                                <select class="form-control" v-model="suscriptor.genero">
                                    <option value="">{{ $t('suscribe.gender') }}</option>
                                    <option value="F">{{ $t('frontend.gender_f') }}</option>
                                    <option value="M">{{ $t('frontend.gender_m') }}</option>
                                    <option value="X">{{ $t('frontend.gender_x') }}</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <datepicker
                                    v-model="suscriptor.fecha_nacimiento"
                                    :placeholder="$t('suscribe.fecha_de_nacimiento')"
                                    id="nacimiento"
                                    lang="es"
                                    format="DD-MM-YYYY"
                                />
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <VueTelInput
                                    v-model="phoneNumber"
                                    :preferredCountries="['mx','ar', 'co', 'pe', 'py', 'ur', 'br', 'cl']"
                                    mode="international"
                                    :defaultCountry="telefonoPaisIso"
                                    :key="telefonoPaisIso"
                                    :inputOptions="{
                                        showDialCode: true,
                                        placeholder: $t('suscribe.phone')
                                    }"
                                    :dropdownOptions="{
                                        showFlags: true,
                                    }"
                                    ref="telInput"
                                    :class="{ 'is-invalid': errores.telefono }"
                                />
                                <small v-if="errores.telefono" class="form-text text-danger">{{ $t('frontend.error') }}&nbsp;<br></small>
                            </div>

                            <div class="col-md-4">
                                <select id="localidad" v-model="suscriptor.idProvincia" class="form-control" required>
                                    <option value="">{{ $t('suscribe.state') }}</option>
                                    <option v-for="provincia in provincias" :key="provincia.id" :value="provincia.id">
                                        {{ provincia.provincia }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <select id="localidad" v-model="suscriptor.idLocalidad" class="form-control" required>
                                    <option value="">{{ $t('suscribe.city') }}</option>
                                    <option v-for="localidad in localidades" :key="localidad.id" :value="localidad.id">
                                        {{ localidad.localidad }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <input
                                    class="form-control"
                                    placeholder="Instagram"
                                    v-model="suscriptor.instagram">
                            </div>
                        </div>

                        <!-- Información Académica -->
                        <h4 class="mt-3">{{ $t('suscribe.academic_info') }}</h4>
                        <select class="form-control" v-model="suscriptor.ocupacion_actual">
                            <option value="">{{ $t('frontend.nivel_de_estudios') }}</option>
                            <option value="secundario"> {{ secundarioLabel }} </option>
                            <option value="universitario">{{ $t('frontend.universitario') }}</option>
                            <option value="posgrado"> {{ $t('frontend.posgrado') }} </option>
                        </select>

                        <!-- Información TECHO -->
                        <h4 class="mt-3">{{ $t('suscribe.how_meet_techo') }}</h4>
                        <select id="canal_contacto" v-model="suscriptor.canal_contacto" class="form-control" required>
                            <option :value="$t('frontend.social_networks')"> {{ $t('frontend.social_networks') }} </option>
                            <option :value="$t('frontend.advertisement_traditional_media')"> {{ $t('frontend.advertisement_traditional_media') }} </option>
                            <option :value="$t('frontend.outdoor_advertising')"> {{ $t('frontend.outdoor_advertising') }} </option>
                            <option :value="$t('frontend.website')"> {{ $t('frontend.website') }} </option>
                            <option :value="$t('frontend.known_person')"> {{ $t('frontend.known_person') }} </option>
                            <option :value="$t('frontend.email_campaign')"> {{ $t('frontend.email_campaign') }} </option>
                            <option :value="$t('frontend.street_intervention')"> {{ $t('frontend.street_intervention') }} </option>
                            <option :value="$t('frontend.event_collection_volunteer_campaign')"> {{ $t('frontend.event_collection_volunteer_campaign') }} </option>
                        </select>

                        <div class="form-check mt-3">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                v-model="suscriptor.experiencia_previa">
                            <label class="form-check-label">
                                {{ $t('suscribe.previous_experience') }}
                            </label>
                        </div>

                        <!-- Preguntas dinámicas de la campaña -->
                        <div v-if="preguntas && preguntas.length" class="mt-3">
                            <h4>{{ $t('suscribe.additional_questions') }}</h4>
                            <div v-for="pregunta in preguntas" v-if="esVisible(pregunta)" :key="pregunta.id" class="form-group mt-2">
                                <label>
                                    {{ pregunta.pregunta }}
                                    <span v-if="pregunta.requerida" class="text-danger">*</span>
                                </label>
                                <p v-if="pregunta.descripcion" class="text-muted small mb-1">{{ pregunta.descripcion }}</p>

                                <select
                                    v-if="pregunta.tipo === 'desplegable'"
                                    class="form-control"
                                    v-model="respuestas[pregunta.id]"
                                    :required="pregunta.requerida"
                                >
                                    <option value="">{{ $t('suscribe.select_option') }}</option>
                                    <option v-for="opcion in pregunta.opciones" :key="opcion" :value="opcion">
                                        {{ opcion }}
                                    </option>
                                </select>

                                <div v-else-if="pregunta.tipo === 'archivo'">
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,application/pdf"
                                        class="form-control-file"
                                        @change="subirArchivo($event, pregunta)"
                                    >
                                    <small class="text-muted d-block">{{ $t('frontend.archivo_formatos') }}</small>
                                    <div v-if="subiendoArchivo[pregunta.id]" class="text-muted small mt-1">
                                        <i class="fa fa-spinner fa-spin"></i> {{ $t('frontend.subiendo_archivo') }}
                                    </div>
                                    <div v-else-if="respuestas[pregunta.id]" class="text-success small mt-1">
                                        <i class="fa fa-check"></i> {{ nombresArchivo[pregunta.id] || $t('frontend.archivo_cargado') }}
                                    </div>
                                    <div v-if="erroresArchivo[pregunta.id]" class="text-danger small mt-1">{{ erroresArchivo[pregunta.id] }}</div>
                                </div>

                                <input
                                    v-else
                                    class="form-control"
                                    v-model="respuestas[pregunta.id]"
                                    :required="pregunta.requerida"
                                >
                            </div>
                        </div>

                        <button
                            class="btn btn-primary btn-lg mt-4 w-100"
                            type="submit"
                            :disabled="enviando"
                        >
                            <span v-if="enviando"><i class="fa fa-spinner fa-spin"></i></span>
                            <span v-else>{{ $t('suscribe.submit') }}</span>
                        </button>
                    </form>
                </div>

                <!-- REQ 3 — Cuando logueado: solo preguntas adicionales -->
                <div v-else>
                    <form @submit.prevent="guardar">
                        <!-- Preguntas dinámicas de la campaña -->
                        <div v-if="preguntas && preguntas.length" class="mt-3">
                            <h4>{{ $t('suscribe.additional_questions') }}</h4>
                            <div v-for="pregunta in preguntas" v-if="esVisible(pregunta)" :key="pregunta.id" class="form-group mt-2">
                                <label>
                                    {{ pregunta.pregunta }}
                                    <span v-if="pregunta.requerida" class="text-danger">*</span>
                                </label>
                                <p v-if="pregunta.descripcion" class="text-muted small mb-1">{{ pregunta.descripcion }}</p>

                                <select
                                    v-if="pregunta.tipo === 'desplegable'"
                                    class="form-control"
                                    v-model="respuestas[pregunta.id]"
                                    :required="pregunta.requerida"
                                >
                                    <option value="">{{ $t('suscribe.select_option') }}</option>
                                    <option v-for="opcion in pregunta.opciones" :key="opcion" :value="opcion">
                                        {{ opcion }}
                                    </option>
                                </select>

                                <div v-else-if="pregunta.tipo === 'archivo'">
                                    <input
                                        type="file"
                                        accept="image/jpeg,image/png,application/pdf"
                                        class="form-control-file"
                                        @change="subirArchivo($event, pregunta)"
                                    >
                                    <small class="text-muted d-block">{{ $t('frontend.archivo_formatos') }}</small>
                                    <div v-if="subiendoArchivo[pregunta.id]" class="text-muted small mt-1">
                                        <i class="fa fa-spinner fa-spin"></i> {{ $t('frontend.subiendo_archivo') }}
                                    </div>
                                    <div v-else-if="respuestas[pregunta.id]" class="text-success small mt-1">
                                        <i class="fa fa-check"></i> {{ nombresArchivo[pregunta.id] || $t('frontend.archivo_cargado') }}
                                    </div>
                                    <div v-if="erroresArchivo[pregunta.id]" class="text-danger small mt-1">{{ erroresArchivo[pregunta.id] }}</div>
                                </div>

                                <input
                                    v-else
                                    class="form-control"
                                    v-model="respuestas[pregunta.id]"
                                    :required="pregunta.requerida"
                                >
                            </div>
                        </div>

                        <button
                            class="btn btn-primary btn-lg mt-4 w-100"
                            type="submit"
                            :disabled="enviando"
                        >
                            <span v-if="enviando"><i class="fa fa-spinner fa-spin"></i></span>
                            <span v-else>{{ $t('suscribe.submit') }}</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- REQ 5 — Mensaje de agradecimiento post-suscripción -->
            <div
                v-if="guardado"
                class="alert alert-success text-center mt-4 p-4"
            >
                <!-- Mensaje personalizado de la campaña (rich text) -->
                <div
                    v-if="campaign && campaign.confirmation_message"
                    v-html="campaign.confirmation_message"
                    class="campaign-confirmation-message mb-2"
                ></div>
                <!-- Fallback: mensaje genérico traducido -->
                <template v-else>
                    <h4 class="mb-2">
                        {{ $t('suscribe.success_title') }}
                    </h4>
                    <p class="mb-1">
                        {{ $t('suscribe.success_message') }}
                    </p>
                    <p class="mb-0">
                        {{ $t('suscribe.success_extra') }}
                    </p>
                </template>
                <a
                    v-if="campaign && campaign.whatsapp_link"
                    :href="campaign.whatsapp_link"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn btn-success mt-3"
                >
                    <i class="fa fa-whatsapp"></i> {{ $t('suscribe.join_whatsapp_group') }}
                </a>
            </div>

            <small class="d-block text-center mt-2">
                {{ $t('suscribe.privacy_notice') }}
                <a href="https://www.techo.org/politicas-de-privacidad" target="_blank">
                    {{ $t('suscribe.privacy_policy') }}
                </a>
            </small>
        </div>
    </div>
</template>

<script>
import { VueTelInput } from 'vue-tel-input';
import { esVisible as evalVisible } from '../../conditionEvaluator';

export default {
    name: 'SuscripcionTecho',
    components: { VueTelInput },
    props: {
        pais: {
            type: Object,
            required: false,
            default: null,
        },
        campaign: {
            type: Object,
            required: false,
            default: null,
        },
        preguntas: {
            type: Array,
            required: false,
            default: function() { return []; },
        },
        // REQ 3 — Usuario logueado (null si no está autenticado)
        user: {
            type: Object,
            required: false,
            default: null,
        },
    },
    data() {
        return {
            suscriptor: {
                nombre:            '',
                apellido:          '',
                genero:            '',
                fecha_nacimiento:  '',
                mail:              '',
                telefono:          '',
                idPais:            '',
                idProvincia:       '',
                idLocalidad:       '',
                ocupacion_actual:  '',
                canal_contacto:    '',
                experiencia_previa: false,
                instagram:         '',
                dni:               '',
                campaign_id:       null,
            },
            provincias:        [],
            localidades:       [],
            phoneNumber:       '',
            previousCountry:   '',
            telefonoPaisIso:   null,
            guardado:          false,
            yaInscripto:       false,
            emailExiste:       false,
            enviando:          false,
            errores:           {},
            respuestas:        {},
            subiendoArchivo:   {},
            nombresArchivo:    {},
            erroresArchivo:    {},
        };
    },
    computed: {
        estaLogueado() {
            return !!this.user;
        },
        formId() {
            return 'suscribe-form-' + (this.campaign ? this.campaign.id : 'default');
        },
        documentoLabel() {
            if (!this.pais || !this.pais.abreviacion) return this.$t('frontend.passport');
            const key = 'suscribe.dni_by_country.' + this.pais.abreviacion;
            return this.$te(key) ? this.$t(key) : this.$t('frontend.passport');
        },
        secundarioLabel() {
            if (!this.pais || !this.pais.abreviacion) return this.$t('frontend.secundario');
            const key = 'suscribe.secundario_by_country.' + this.pais.abreviacion;
            return this.$te(key) ? this.$t(key) : this.$t('frontend.secundario');
        },
    },
    mounted() {
        // Pre-inicializar `respuestas` con una clave reactiva por pregunta. En Vue 2
        // agregar claves nuevas a un objeto no es reactivo, así que sin esto responder
        // la pregunta padre no dispara el re-render que revela las condicionales.
        (this.preguntas || []).forEach((p) => {
            if (p && p.id != null) this.$set(this.respuestas, p.id, '');
        });
        this.removeUndefinedText();
        if (this.pais) {
            this.suscriptor.idPais = this.pais.id;
            this.traer_provincias();
            this.telefonoPaisIso = this.pais.iso2 ? this.pais.iso2 : null;
        }
        if (this.campaign) {
            this.suscriptor.campaign_id = this.campaign.id;
        }
        // REQ 3 — Pre-llenar con datos del usuario logueado
        if (this.user) {
            this.suscriptor.nombre    = this.user.nombre   || '';
            this.suscriptor.apellido  = this.user.apellido || '';
            this.suscriptor.mail      = this.user.mail     || '';
            this.suscriptor.telefono  = this.user.telefono || '';
        }
    },
    watch: {
        phoneNumber(val) {
            if (!val) {
                this.suscriptor.telefono = '';
                return;
            }
            this.suscriptor.telefono = val.replace(/\s+/g, '');
        },
        telefonoPaisIso() {
            this.removeUndefinedText();
        },
        'suscriptor.idProvincia': function() {
            this.traer_localidades();
        },
    },
    methods: {
        // ¿La pregunta debe mostrarse según sus condiciones?
        // En suscribe, `respuestas` ya es un mapa { pregunta_id: texto }.
        esVisible(pregunta) {
            return evalVisible(pregunta, this.preguntas || [], this.respuestas || {});
        },

        // Sube el archivo de una pregunta tipo 'archivo' (endpoint público de
        // campaña) y guarda el path devuelto como valor de la respuesta.
        subirArchivo(event, pregunta) {
            const file = event.target.files && event.target.files[0];
            this.$set(this.erroresArchivo, pregunta.id, '');
            if (!file) return;

            const tiposOk = ['image/jpeg', 'image/png', 'application/pdf'];
            if (tiposOk.indexOf(file.type) === -1) {
                this.$set(this.erroresArchivo, pregunta.id, this.$t('frontend.archivo_tipo_invalido'));
                event.target.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.$set(this.erroresArchivo, pregunta.id, this.$t('frontend.archivo_muy_grande'));
                event.target.value = '';
                return;
            }

            const fd = new FormData();
            fd.append('archivo', file);
            fd.append('pregunta_id', pregunta.id);

            const url = this.pais
                ? ('/' + this.pais.abreviacion + '/suscribe/pregunta-archivo')
                : '/suscribe/pregunta-archivo';

            this.$set(this.subiendoArchivo, pregunta.id, true);
            axios.post(url, fd)
                .then((response) => {
                    this.$set(this.respuestas, pregunta.id, response.data.path);
                    this.$set(this.nombresArchivo, pregunta.id, response.data.nombre);
                })
                .catch(() => {
                    this.$set(this.respuestas, pregunta.id, '');
                    this.$set(this.erroresArchivo, pregunta.id, this.$t('frontend.archivo_error'));
                    event.target.value = '';
                })
                .finally(() => {
                    this.$set(this.subiendoArchivo, pregunta.id, false);
                });
        },

        // REQ 2 — Verificar si el email ya existe como usuario
        verificarEmail() {
            const email = this.suscriptor.mail;
            if (!email || !this.pais) return;
            axios.get('/' + this.pais.abreviacion + '/check-email', { params: { email: email } })
                .then(function(response) {
                    this.emailExiste = response.data.exists;
                }.bind(this))
                .catch(function() {});
        },

        guardar(e) {
            if (e && e.target && e.target.checkValidity && !e.target.checkValidity()) {
                e.target.reportValidity();
                return;
            }

            // Validar teléfono solo si no está logueado
            if (!this.estaLogueado) {
                this.errores.telefono = false;
                if (!this.suscriptor.telefono || this.suscriptor.telefono.length <= 6) {
                    this.errores.telefono = true;
                    return;
                }
            }

            // Validar preguntas archivo obligatorias visibles: el input file no
            // lleva atributo required (se sube async), así que checkValidity no
            // las cubre. Se marca el error inline en cada pregunta que falte.
            let faltaArchivo = false;
            (this.preguntas || []).forEach((p) => {
                if (p.tipo === 'archivo' && p.requerida && this.esVisible(p) && !this.respuestas[p.id]) {
                    this.$set(this.erroresArchivo, p.id, this.$t('frontend.archivo_requerido'));
                    faltaArchivo = true;
                }
            });
            if (faltaArchivo) {
                return;
            }

            this.enviando = true;

            // Solo enviar respuestas de preguntas visibles (las ocultas por
            // condición se descartan; el backend igualmente las prunea).
            const visibles = {};
            (this.preguntas || []).forEach(function(p) {
                visibles[p.id] = this.esVisible(p);
            }.bind(this));

            const respuestasArray = Object.keys(this.respuestas)
                .filter(function(preguntaId) {
                    return visibles[preguntaId] !== false;
                })
                .map(function(preguntaId) {
                    return {
                        pregunta_id: parseInt(preguntaId),
                        respuesta:   this.respuestas[preguntaId],
                    };
                }.bind(this));

            const payload = Object.assign({}, this.suscriptor, {
                fecha_nacimiento: this.formatFecha(this.suscriptor.fecha_nacimiento),
                respuestas: respuestasArray,
            });

            const postUrl = this.pais ? ('/' + this.pais.abreviacion + '/suscribe') : '/suscribe';

            axios.post(postUrl, payload)
                .then(function() {
                    this.guardado = true;
                }.bind(this))
                .catch(function(error) {
                    // REQ 4 — Manejar duplicado
                    if (error.response && error.response.status === 422
                        && error.response.data && error.response.data.already_registered) {
                        this.yaInscripto = true;
                    }
                }.bind(this))
                .finally(function() {
                    this.enviando = false;
                }.bind(this));
        },

        traer_provincias() {
            if (this.suscriptor.idPais) {
                axios.get('/ajax/paises/' + this.suscriptor.idPais + '/provincias').then(function(response) {
                    this.provincias = response.data;
                    this.provinciaSeleccionada = null;
                    this.localidades = [];
                }.bind(this));
            }
        },

        traer_localidades() {
            if (this.suscriptor.idPais && this.suscriptor.idProvincia) {
                axios.get('/ajax/paises/' + this.suscriptor.idPais + '/provincias/' + this.suscriptor.idProvincia + '/localidades')
                    .then(function(response) {
                        this.localidades = response.data;
                        this.localidadSeleccionada = null;
                    }.bind(this));
            }
        },

        handleCountryChange(countryData) {
            const currentDialCode = this.previousCountry ? this.previousCountry.dialCode : '';
            const newDialCode = countryData.dialCode;
            if (currentDialCode === '' && this.suscriptor.telefono.startsWith('+')) {
                this.previousCountry = countryData.dialCode;
            } else {
                const phoneNumberWithoutCountryCode = this.phoneNumber.replace('+' + currentDialCode, '').trim();
                this.suscriptor.telefono = '+' + newDialCode + phoneNumberWithoutCountryCode;
                this.phoneNumber = this.suscriptor.telefono;
                this.previousCountry = newDialCode;
            }
        },

        removeUndefinedText() {
            this.$nextTick(function() {
                const selection = this.$el.querySelector('.vti__selection');
                if (!selection) return;
                selection.childNodes.forEach(function(node) {
                    if (node.nodeType === Node.TEXT_NODE && node.nodeValue && node.nodeValue.trim() === 'undefined') {
                        node.nodeValue = '';
                    }
                });
            }.bind(this));
        },

        formatFecha(date) {
            if (!date) return null;
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return yyyy + '-' + mm + '-' + dd;
        },
    },
};
</script>

<style scoped>
.is-invalid {
    border: 2px solid #dc3545 !important;
}
</style>
