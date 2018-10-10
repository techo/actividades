<template>
    <div class="col-md-8">
        <h4>Evalúa la actividad</h4>
        <p class="text-muted">
            Tu opinión nos ayuda a mejorar como organización y a hacer que las actividades sean cada vez más provechosas.
        </p>
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
                            Evaluación de {{ actividad.nombreActividad }}
                            <span v-show="abierto" class="pull-right"><i class="fa fa-chevron-up"></i></span>
                            <span v-show="!abierto" class="pull-right"><i class="fa fa-chevron-down"></i></span>
                        </h6>
                    </div>
                </div>

                <div id="cardEvaluacion" class="collapse" aria-labelledby="headingOne" :data-parent="'evaluaciones_' + actividad.idActividad">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="slider">Puntaje General <div v-show="!noAplica" class="infoPuntaje text-center" :style="{ 'background-color': colorPuntaje}">{{ puntaje }}</div></label>
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
                                        No Aplica / No tengo opinión
                                    </label>
                                    <input type="checkbox" id="noAplica" :value="1" v-model="noAplica" :disabled="evaluacionPasada || enviado">
                                </div>
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="comentarios">Comentarios</label>
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
                            Enviar Evaluación
                        </button>
                        <p class="pull-right" v-if="enviado"><strong>¡Gracias por tu opinión!</strong></p>
                        <p class="pull-right" v-if="evaluacionPasada && !enviado">La fecha de fin de las evaluaciones ya pasó &#9785;</p>
                        <p class="red" v-if="error">
                            <i class="fas fa-exclamation"></i> &nbsp;
                            <i style="margin-left: 0.5em">
                                No se pudo guardar la evaluación. Intentalo de nuevo más tarde.
                            </i>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VueSlider from 'vue-slider-component'; //https://github.com/NightCatSama/vue-slider-component
    export default {
        name: "evaluarActividad",
        components: {
            'vue-slider': VueSlider
        },
        props: ['prop-actividad', 'respuesta'],
        created: function () {
          this.actividad = JSON.parse(this.propActividad);
          if (this.respuesta) {
              this.respuestaAnterior = JSON.parse(this.respuesta);
              this.puntaje = this.respuestaAnterior.puntaje;
              if (this.puntaje === null) { this.noAplica = true; this.puntaje = 5; }
              this.comentario = this.respuestaAnterior.comentario;
              this.enviado = true;
          }
        },
        data: function () {
            return {
                puntaje: 5,
                actividad: {},
                comentario: '',
                abierto: true,
                noAplica: false,
                error: false,
                enviado: false,
            }
        },
        methods: {
            enviarEvaluacion: function () {
                this.abierto = !this.abierto;
                let url = '/actividades/' + this.actividad.idActividad + '/evaluaciones';
                let payload = {
                    idActividad: this.actividad.idActividad,
                    puntaje: (this.noAplica) ? null : this.puntaje,
                    comentario: this.comentario
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
        width: 20px;
        height: 20px;
        background: gray;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        padding-top: 3px;
        margin-left: 3px;
    }
</style>