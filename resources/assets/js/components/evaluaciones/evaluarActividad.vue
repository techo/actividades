<template>
    <div>
        <div :id="'evaluaciones_' + actividad.idActividad">
            <div class="card"  style="width: 100%">
                <div class="card-header accordion" id="headingOne">
                    <h5 class="mb-0">
                        <h6
                                data-toggle="collapse"
                                data-target="#cardEvaluacion"
                                aria-expanded="true"
                                aria-controls="cardEvaluacion"
                                @click="enviarEvaluacion"
                        >
                            Evaluación de {{ actividad.nombreActividad }}
                            <span v-show="abierto"><i class="fa fa-chevron-up"></i></span>
                            <span v-show="!abierto"><i class="fa fa-chevron-down"></i></span>
                        </h6>
                    </h5>
                </div>

                <div id="cardEvaluacion" class="collapse show" aria-labelledby="headingOne" data-parent="#evaluaciones">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="slider">Puntaje General</label>
                                    <vue-slider
                                            :min=1
                                            :max=10
                                            :interval=1
                                            ref="slider"
                                            id="slider"
                                            :disabled="noAplica"
                                            v-model="puntaje"
                                    >
                                    </vue-slider>
                                </div>

                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="noAplica" style="margin-right: 2em; margin-top: 2em">No Aplica / No tengo opinión </label>
                                    <input type="checkbox" id="noAplica" :value="1" v-model="noAplica">
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
                            >{{ comentario }}</textarea>
                        </div>
                        <button class="btn btn-primary pull-right" v-if="!enviado" @click.prevent="enviarEvaluacion">
                            Enviar Evaluación
                        </button>
                        <p class="pull-right" v-else><strong>¡Gracias por tu opinión!</strong></p>
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
</style>