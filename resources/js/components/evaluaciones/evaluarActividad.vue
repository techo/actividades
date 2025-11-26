<template>
    <div>
        <p class="text-muted">
            {{ $t('frontend.feedback_text') }}
        </p>
        <h4 class="subtitle">{{ $t('frontend.activity_feedback') }}</h4>
        <div :id="'evaluaciones_' + actividad.idActividad">
            <div class="card"  style="width: 100%">
                <div class="card-header accordion" id="headingOne">
                    <div class="mb-0">
                        <h6
                                data-toggle="collapse"
                                data-target="#cardEvaluacion"
                                aria-expanded="true"
                                aria-controls="cardEvaluacion"
                                @click="cambiarIcono"
                        >
                            {{ $t('frontend.feedback_of') }} {{ actividad.nombreActividad }}
                            <span v-show="!abierto" class="pull-right"><i class="fa fa-chevron-up"></i></span>
                            <span v-show="abierto" class="pull-right"><i class="fa fa-chevron-down"></i></span>
                        </h6>
                    </div>
                </div>

                <div id="cardEvaluacion" class="collapse show" aria-labelledby="headingOne" :data-parent="'evaluaciones_' + actividad.idActividad">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="slider">{{ $t('frontend.general_score') }} <div v-show="!noAplica" class="infoPuntaje text-center" :style="{ 'background-color': colorPuntaje}">{{ puntaje }}</div></label>
                                    <input type="range"
                                           class="form-control-range"
                                           id="slider"
                                           min="1"
                                           max="10"
                                           step="1"
                                           :disabled="noAplica || evaluacionPasada || enviado"
                                           v-model="puntaje">
                                </div>

                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="noAplica" style="margin-right: 2em; margin-top: 2em" :class="{'gris': evaluacionPasada }">
                                        {{ $t('frontend.doesn_not_apply') }}
                                    </label>
                                    <input type="checkbox" id="noAplica" :value="1" v-model="noAplica" :disabled="evaluacionPasada || enviado">
                                </div>
                            </div>

                        </div>

                        <!-- ICONOS - ATRIBUTOS POSITIVOS -->
                        <div class="form-group" v-show="puntaje>6">
                            <label>{{ $t('evaluacion.titulo_positivos') }}</label>
                            <div class="icons-line" :class="{ disabled: evaluacionPasada || enviado }" role="list">
                                <div
                                  v-for="([key, text], idx) in atributosArray"
                                  :key="key"
                                  class="icon-item"
                                  :class="{ seleccionado: tagsPositivos.includes(key) }"
                                  @click="!isDisabled && toggleTag(key, 'positivo')"
                                  :aria-pressed="tagsPositivos.includes(key)"
                                  :title="text"
                                  role="listitem"
                                >
                                  <img :src="iconPath(key)" :alt="text" />
                                  <small class="icon-label">{{ text }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- ICONOS - PUNTOS A MEJORAR (NEGATIVOS) -->
                        <div class="form-group" v-show="puntaje<8">
                            <label>{{ $t('evaluacion.titulo_negativos') }}</label>
                            <div class="icons-line" :class="{ disabled: evaluacionPasada || enviado }" role="list">
                                <div
                                  v-for="([key, text], idx) in mejorasArray"
                                  :key="key"
                                  class="icon-item negativo"
                                  :class="{ seleccionado: tagsNegativos.includes(key) }"
                                  @click="!isDisabled && toggleTag(key, 'negativo')"
                                  :aria-pressed="tagsNegativos.includes(key)"
                                  :title="text"
                                  role="listitem"
                                >
                                  <small class="icon-label">{{ text }}</small>
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
                        <p class="pull-right" v-if="evaluacionPasada && !enviado"> {{ $t('frontend.feedback_date_expired')}}</p>
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
        name: "evaluarActividad",
        components: {
            'vue-slider': VueSlider
        },
        props: ['prop-actividad', 'respuesta'],
        created: function () {
          // actividad viene como JSON string en tu implementación original
          this.actividad = JSON.parse(this.propActividad);

          // cargar traducciones (debe existir evaluacion.atributos y evaluacion.mejoras)
          // this.$t puede devolver un objeto { key: texto, ... }
          this.atributos = this.$t('evaluacion.atributos_actividad') || {};
          this.mejoras = this.$t('evaluacion.mejoras_actividad') || {};

          if (this.respuesta) {
              this.respuestaAnterior = JSON.parse(this.respuesta);
              // puntaje puede ser null (no aplica)
              this.puntaje = (this.respuestaAnterior.puntaje === null) ? 5 : this.respuestaAnterior.puntaje;
              if (this.respuestaAnterior.puntaje === null) {
                this.noAplica = true;
              }
              this.comentario = this.respuestaAnterior.comentario || '';
              // cargar tags si existen (espera array de códigos)
              this.tagsPositivos = this.respuestaAnterior.tags_positivos || [];
              this.tagsNegativos = this.respuestaAnterior.tags_negativos || [];
              this.enviado = true;
          }
        },
        data: function () {
            return {
                puntaje: null,
                actividad: {},
                comentario: '',
                abierto: true,
                noAplica: false,
                error: false,
                enviado: false,
                atributos: {},   // objeto key => texto
                mejoras: {},     // objeto key => texto
                tagsPositivos: [],
                tagsNegativos: []
            }
        },
        methods: {
            enviarEvaluacion: function () {
                this.abierto = !this.abierto;
                let url = '/actividades/' + this.actividad.idActividad + '/evaluaciones';
                let payload = {
                    idActividad: this.actividad.idActividad,
                    puntaje: (this.noAplica) ? null : this.puntaje,
                    comentario: this.comentario,
                    tags_positivos: this.tagsPositivos,
                    tags_negativos: this.tagsNegativos
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
            cambiarIcono: function () {
                this.abierto = !this.abierto;
            },
            toggleTag(key, tipo) {
              if (tipo === 'positivo') {
                this.tagsPositivos = this.toggleItem(this.tagsPositivos, key);
              } else {
                this.tagsNegativos = this.toggleItem(this.tagsNegativos, key);
              }
            },
            toggleItem(array, key) {
              return array.includes(key)
                ? array.filter(k => k !== key)
                : [...array, key];
            },
            iconPath(key) {
              // Ruta pública para los íconos; asegurate de subir archivos con el mismo key + extensión
              return `/img/evaluacion/${key}.png`;
            }
        },
        computed: {
            colorPuntaje: function() {
                if (this.puntaje <= 3) {
                    return '#F7977A';
                } else if (this.puntaje <= 6) {
                    return '#FFF79A';
                } else if (this.puntaje <= 10) {
                    return '#82CA9D';
                }
            },
            evaluacionPasada: function () {
                let ahora = new Date();
                let fechaFinEvaluaciones = new Date(this.actividad.fechaFinEvaluaciones);
                return ahora.getTime() > fechaFinEvaluaciones.getTime();
            },
            atributosArray() {
              // devuelve [ [key, texto], ... ] para v-for
              return Object.entries(this.atributos || {});
            },
            mejorasArray() {
              return Object.entries(this.mejoras || {});
            },
            isDisabled() {
              return this.evaluacionPasada || this.enviado;
            }
        }
    }
</script>

<style scoped>
    .subtitle {
        margin: 25px 0;
    }
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
        width: 20px;
        height: 20px;
        background: gray;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        padding-top: 3px;
        margin-left: 3px;
    }

    .icons-line {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
    }

    .icon-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    text-align: center;
    width: 110px; /* ajustá según el espacio disponible */
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    padding: 0.5rem;
    border-radius: 12px;
    background: #f8f8f8;
    min-height: 130px; /* para dar espacio al texto */
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
    font-size: 0.85rem;
    color: #444;
    line-height: 1.2;
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
</style>
