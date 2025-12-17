<template>
    <div class="col-md-12">
        <div class="accordion" :id="'evaluacionPersona_' + persona.idPersona">
            <div class="card"  style="width: 100%">
                <div class="card-header" id="headingOne">
                    <div class="mb-0">
                        <h6
                                data-toggle="collapse"
                                :data-target="'#cardEvaluacionPersona_'+ persona.idPersona"
                                aria-expanded="true"
                                :aria-controls="'cardEvaluacionPersona_'+ persona.idPersona"
                                @click="cambiarIcono"
                        >
                            {{ $t('frontend.feedback_of') }} {{ nombre }} <span v-if="persona.rol"> - {{ persona.rol }} </span>
                            <span v-show="abierto" class="pull-right"><i class="fa fa-chevron-down"></i></span>
                            <span v-show="!abierto" class="pull-right"><i class="fa fa-chevron-up"></i></span>
                        </h6>
                    </div>
                </div>

                <div
                        :id="'cardEvaluacionPersona_'+ persona.idPersona"
                        class="collapse"
                        :data-parent="'#evaluacionPersona_' + persona.idPersona"
                >
                    <div class="card-body">
               <!--         <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="sliderTecnico">{{ $t('frontend.technical_score') }}<div v-show="!noAplicaTecnico" class="infoPuntaje text-center" :style="{ 'background-color': colorPuntajeTecnico}">{{ puntajeTecnico }}</div></label>
                                    <p style="font-size: 12px; color: #6d6d6c" >{{ $t('frontend.technical_score_description') }}</p>
                                    <input type="range"
                                           class="form-control-range"
                                           id="sliderTecnico"
                                           min="1"
                                           max="10"
                                           step="1"
                                           :disabled="noAplicaTecnico || evaluacionPasada || enviado"
                                           v-model="puntajeTecnico"
                                    >
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="noAplicaTecnico" style="margin-right: 2em; margin-top: 2em" :class="{'gris': evaluacionPasada }">
                                        {{ $t('frontend.doesn_not_apply') }}
                                    </label>
                                    <input
                                            type="checkbox"
                                            id="noAplicaTecnico"
                                            :value="1"
                                            :disabled="evaluacionPasada || enviado"
                                            v-model="noAplicaTecnico"
                                    >
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="sliderSocial">{{ $t('frontend.social_score') }}<div v-show="!noAplicaSocial" class="infoPuntaje text-center" :style="{ 'background-color': colorPuntajeSocial}">{{ puntajeSocial }}</div></label>
                                    <p style="font-size: 12px; color: #6d6d6c;" >{{ $t('frontend.social_score_description') }}</p>
                                    <input type="range"
                                           class="form-control-range"
                                           id="sliderSocial"
                                           min="1"
                                           max="10"
                                           step="1"
                                           :disabled="noAplicaSocial || evaluacionPasada || enviado"
                                           v-model="puntajeSocial"
                                    >
                                </div>

                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="noAplicaSocial" style="margin-right: 2em; margin-top: 2em" :class="{'gris': evaluacionPasada }">
                                        {{ $t('frontend.doesn_not_apply') }}
                                    </label>
                                    <input
                                            type="checkbox"
                                            id="noAplicaSocial"
                                            :value="1"
                                            :disabled="evaluacionPasada || enviado"
                                            v-model="noAplicaSocial"
                                    >
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="sliderGenero">{{ $t('frontend.gender_score') }}<div v-show="!noAplicaGenero" class="infoPuntaje text-center" :style="{ 'background-color': colorPuntajeGenero}">{{ puntajeGenero }}</div></label>
                                    <p style="font-size: 12px; color: #6d6d6c;" >{{ $t('frontend.gender_score_description') }}</p>
                                    <input type="range"
                                           class="form-control-range"
                                           id="sliderGenero"
                                           min="1"
                                           max="10"
                                           step="1"
                                           :disabled="noAplicaGenero || evaluacionPasada || enviado"
                                           v-model="puntajeGenero"
                                    >
                                </div>

                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="noAplicaGenero" style="margin-right: 2em; margin-top: 2em" :class="{'gris': evaluacionPasada }">
                                        {{ $t('frontend.doesn_not_apply') }}
                                    </label>
                                    <input
                                            type="checkbox"
                                            id="noAplicaGenero"
                                            :value="1"
                                            :disabled="evaluacionPasada || enviado"
                                            v-model="noAplicaGenero"
                                    >
                                </div>
                            </div>

                        </div>-->

                       <div v-for="(pregunta, keyPregunta) in preguntas" :key="keyPregunta" class="mb-4">
                            <!-- TÍTULO Y DESCRIPCIÓN -->
                            <label :for="'slider-' + keyPregunta">
                                {{ pregunta.title }}
                                
                            </label>
                            <p class="desc">{{ pregunta.desc }}</p>

                            <div class="infoPuntaje" :style="{ backgroundColor: colorPuntaje(puntajes[keyPregunta]) }">
                            {{ puntajes[keyPregunta] }}
                            </div>

                            <!-- SLIDER -->
                            <input type="range"
                                class="form-control-range"
                                :id="'slider-' + keyPregunta"
                                min="1"
                                max="10"
                                step="1"
                                v-model="puntajes[keyPregunta]"
                                :disabled="evaluacionPasada || enviado"
                            >

                            <!-- TAGS POSITIVOS -->
                            <div class="form-group" v-if="puntajes[keyPregunta] >= 7">
                                <label>{{ $t('evaluacion.titulo_positivos') }}</label>

                                <div class="icons-line" role="list">
                                <span
                                    v-for="(text, keyTag) in tags[keyPregunta].positivos"
                                    :key="keyTag"
                                    class="icon-item positivo"
                                    :class="{ seleccionado: tagsSeleccionados[keyPregunta].positivos.includes(keyTag) ,
                                                deshabilitado: evaluacionPasada || enviado}"
                                    @click="!evaluacionPasada && !enviado && toggleTag(keyPregunta, keyTag, 'positivos')"
                                >
                                    {{ text }}
                                </span>
                                </div>
                            </div>
                                                    
                            <!-- TAGS NEGATIVOS -->
                            <div class="form-group" v-if="puntajes[keyPregunta] <= 6">
                                <label>{{ $t('evaluacion.titulo_negativos') }}</label>

                                <div class="icons-line" role="list">
                                <span
                                    v-for="(text, keyTag) in tags[keyPregunta].negativos"
                                    :key="keyTag"
                                                            class="icon-item negativo"
                                    :class="{ seleccionado: tagsSeleccionados[keyPregunta].negativos.includes(keyTag) ,
                                                deshabilitado: evaluacionPasada || enviado}"
                                    @click="!evaluacionPasada && !enviado && toggleTag(keyPregunta, keyTag, 'negativos')"
                                >
                                    {{ text }}
                                </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comentarios">{{ $t('frontend.comments') }}</label>
                            <textarea
                                    name="comentarios"
                                    id="comentarios"
                                    cols="30"
                                    rows="5"
                                    class="form-control"
                                    v-model="comentario"
                                    :disabled="evaluacionPasada || enviado"
                            >{{ comentario }}</textarea>
                        </div>

                        <button
                                class="btn btn-primary pull-right"
                                v-if="!enviado && !evaluacionPasada"
                                @click="enviarEvaluacion"
                        >
                            {{ $t('frontend.send') }}
                        </button>
                        <p class="pull-right" v-if="enviado"><strong>{{ $t('frontend.sent') }}</strong></p>
                        <p class="pull-right" v-if="evaluacionPasada & !enviado">{{ $t('frontend.unable_to_sent_feeback') }}</p>
                        <p class="red" v-if="error">
                            <i class="fas fa-exclamation"></i> &nbsp;
                            <i style="margin-left: 0.5em">
                                {{ $t('frontend.unable_to_sent_feeback') }}
                            </i>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    import VueSlider from 'vue-slider-component';
    export default {
        name: "evaluarPersona",
        components: {
            'vue-slider': VueSlider
        },
        props: ['persona', 'actividad', 'respuestas'],
        created: function () {
            this.preguntas = this.$t('evaluacion.personas.preguntas') || {};
            this.tags = this.$t('evaluacion.personas.tags') || {};
            // Si puntaje Social existe, puntaje tecnico también
            if (this.persona.puntajeSocial !== undefined) {
                this.puntajeTecnico = this.persona.puntajeTecnico;
                this.puntajeSocial = this.persona.puntajeSocial;
                this.puntajeGenero = this.persona.puntajeGenero;
                if (this.puntajeTecnico === null) { this.noAplicaTecnico = true; }
                if (this.puntajeSocial === null) { this.noAplicaSocial = true; }
                if (this.puntajeGenero === null) { this.noAplicaGenero = true; }
                this.comentario = this.persona.comentario;
                this.enviado = true;
            }
        },
        data: function () {
            return {
                puntajeSocial: null,
                puntajeTecnico: null,
                puntajeGenero: null,
                comentario: '',
                abierto: true,
                noAplicaTecnico: false,
                noAplicaSocial: false,
                noAplicaGenero: false,
                error: false,
                enviado: false,
                preguntas: [],
                tags: [],
                puntajes: {},
                tagsSeleccionados: {}
            }
        },

        mounted() {
            // inicializar estructuras dinámicas
            Object.keys(this.preguntas).forEach((key) => {
            this.$set(this.puntajes, key, 9); // puntaje por defecto
            this.$set(this.tagsSeleccionados, key, {
                positivos: [],
                negativos: []
            });
            });
            this.cargarRespuestasIniciales();
        },


        watch: {
            respuestas: {
                deep: true,
                handler() {
                    this.cargarRespuestasIniciales();
                }
            }
        },
        methods: {
            enviarEvaluacion: function () {
                this.abierto = !this.abierto;
                let url = '/actividades/' + this.actividad.idActividad + '/persona/' + this.persona.idPersona + '/evaluar';
                let payload = {
                    idActividad: this.actividad.idActividad,
                    puntajeTecnico: (this.noAplicaTecnico) ? null : this.puntajeTecnico,
                    puntajeSocial: (this.noAplicaSocial) ? null : this.puntajeSocial,
                    puntajeGenero: (this.noAplicaGenero) ? null : this.puntajeGenero,
                    comentario: this.comentario,
                    evaluado: this.persona,
                    noAplicaSocial: this.noAplicaSocial,
                    noAplicaTecnico: this.noAplicaTecnico,
                    noAplicaGenero: this.noAplicaGenero,
                    puntajes: this.puntajes,
                    tagsSeleccionados: this.tagsSeleccionados
                };
                this.axiosPost(url,
                    function(response, self) {
                        self.enviado = true;
                        self.error = false;
                    },
                    payload,
                    function(response, self) {
                        self.error = true;
                    }
                );
            },
            cargarRespuestasIniciales() {
                if (!this.respuestas || this.respuestas.length === 0) return;

                this.respuestas.forEach(resp => {
                    const key = resp.question_key;

                    // Puntaje
                    this.$set(this.puntajes, key, resp.score);

                    // Tags Seleccionados
                    this.$set(this.tagsSeleccionados, key, {
                        positivos: resp.tags_positivos || [],
                        negativos: resp.tags_negativos || []
                    });
                });
            },
            cambiarIcono: function () {
                this.abierto = !this.abierto;
            },
            toggleTag(pregunta, tagKey, tipo) {
                const arr = this.tagsSeleccionados[pregunta][tipo];

                if (arr.includes(tagKey)) {
                    this.tagsSeleccionados[pregunta][tipo] =
                    arr.filter((x) => x !== tagKey);
                } else {
                    arr.push(tagKey);
                }
            },

            iconPath(tagKey) {
                return `/images/tags/${tagKey}.png`;
            },

            colorPuntaje(v) {
                if (v <= 3) return "#F7977A";
                if (v <= 6) return "#FFF79A";
                return "#82CA9D";
            }
        },
        computed: {
            colorPuntajeTecnico: function() {
                if (this.puntajeTecnico <= 3) {
                    return '#F7977A';
                } else if (this.puntajeTecnico <= 6) {
                    return '#FFF79A';
                } else if (this.puntajeTecnico <= 10) {
                    return '#82CA9D';
                }
            },
            colorPuntajeSocial: function() {
                if (this.puntajeSocial <= 3) {
                    return '#F7977A';
                } else if (this.puntajeSocial <= 6) {
                    return '#FFF79A';
                } else if (this.puntajeSocial <= 10) {
                    return '#82CA9D';
                }
            },
            colorPuntajeGenero: function() {
                if (this.puntajeGenero <= 3) {
                    return '#F7977A';
                } else if (this.puntajeGenero <= 6) {
                    return '#FFF79A';
                } else if (this.puntajeGenero <= 10) {
                    return '#82CA9D';
                }
            },
            nombre: function () {
                if (this.persona.nombres !== undefined) {
                    return this.persona.nombres + ' ' + this.persona.apellidoPaterno;
                }
                return this.persona.nombre;
            },
            evaluacionPasada: function () {
                let ahora = new Date();
                let fechaFinEvaluaciones = new Date(this.actividad.fechaFinEvaluaciones);
                return ahora.getTime() > fechaFinEvaluaciones.getTime();
            }
        }
    }
</script>

<style scoped>
    .collapse {
        border-bottom: solid thin #d9dde2;
        padding-bottom: 2em;
    }

    .red {
        color: red;
    }

    .gris {
        color: #8F8F8F;
    }

    .infoPuntaje {
        border-radius: 50%;
        width: 40px;
        height: 40px;
        background: gray;
        display: table-cell;
        text-align: center;
        vertical-align: middle;
        font-size: medium;
        padding-top: 3px;
        margin-left: 3px;
    }
.pregunta-card { margin-bottom: 2rem; }
 .icons-line {
    display: flex;
    flex-wrap: nowrap;
    overflow-x: auto;
    gap: 0.75rem;
    padding-bottom: 0.5rem;

    -webkit-overflow-scrolling: touch;
    /* Opcional: oculta scroll feo */
    scrollbar-width: thin;
    -ms-overflow-style: none;
    scroll-snap-type: x mandatory;
}

/* Opcional: scroll estilizado */
.icons-line::-webkit-scrollbar {
    height: 6px;
}
.icons-line::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}

.icon-item {
    flex: 0 0 auto;  /* ❗ Fundamental para scroll horizontal */
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;

    width: 95px; /* más compacto */
    padding: 0.4rem 0.5rem;

    background: #f8f8f8;
    border-radius: 10px;
    cursor: pointer;
    scroll-snap-align: start;

    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

/* Imagen o ícono dentro */
.icon-item img,
.icon-item .icon {
    width: 40px;     /* achicado */
    height: 40px;
    object-fit: contain;
    margin-bottom: 0.3rem;
}

/* Texto: que fluya en varias líneas sin cortar */
.icon-text {
    font-size: 0.42rem;
    line-height: 1.1rem;
    white-space: normal;        /* ❗ Hace que no se corte */
    word-break: break-word;     /* ❗ Evita overflow */
    max-width: 100%;
}

    .icon-item:hover {
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .icon-item img {
    width: 50px;
    height: 50px;
    object-fit: contain;
    margin-bottom: 0.5rem;
    }

    .icon-label {
    font-size: 0.7rem;
    color: #444;
    line-height: 1;
    word-wrap: break-word;
    white-space: normal; /* 🔹 Permite varias líneas */
    overflow-wrap: break-word;
    }

    /* Estado seleccionado */
    .icon-item.seleccionado {
    border: 2px solid #007bff;
    background-color: #e8f0ff;
    }

    /* Estado deshabilitado */
    .icons-line.disabled {
    opacity: 0.5;
    pointer-events: none;
    }

    .icon-item.negativo.seleccionado {
      background: #fff0f0;
      box-shadow: 0 4px 10px rgba(185, 64, 64, 0.06);
    }

    .icon-item.deshabilitado {
    opacity: 0.4;
    cursor: not-allowed;
    pointer-events: none; /* si querés bloquear click 100% desde CSS */
}
</style>