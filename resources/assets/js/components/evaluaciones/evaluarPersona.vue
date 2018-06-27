<template>
    <div>
        <div class="accordion" :id="'evaluacionPersona_' + persona.idPersona">
            <div class="card"  style="width: 100%">
                <div class="card-header" id="headingOne">
                    <span class="mb-0">
                        <h6
                                data-toggle="collapse"
                                :data-target="'#cardEvaluacionPersona_'+ persona.idPersona"
                                aria-expanded="true"
                                :aria-controls="'cardEvaluacionPersona_'+ persona.idPersona"
                                @click="cambiarIcono"
                        >
                            Evaluación de {{ persona.nombre }} - {{ persona.rol }}
                            <span v-show="abierto" class="pull-right"><i class="fa fa-chevron-up"></i></span>
                            <span v-show="!abierto" class="pull-right"><i class="fa fa-chevron-down"></i></span>
                        </h6>
                    </span>
                </div>

                <div :id="'cardEvaluacionPersona_'+ persona.idPersona" class="collapse show" aria-labelledby="headingOne" :data-parent="'#evaluacionPersona_' + persona.idPersona">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="sliderTecnico">Puntaje Técnico</label>
                                    <vue-slider
                                            :min=1
                                            :max=10
                                            :interval=1
                                            :piecewise=true
                                            ref="sliderTecnico"
                                            id="sliderTecnico"
                                            :disabled="noAplicaTecnico"
                                            v-model="puntajeTecnico"
                                    >
                                    </vue-slider>
                                </div>

                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="noAplicaTecnico" style="margin-right: 2em; margin-top: 2em">No Aplica / No tengo opinión </label>
                                    <input type="checkbox" id="noAplicaTecnico" :value="1" v-model="noAplicaTecnico">
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="sliderSocial">Puntaje Social</label>
                                    <vue-slider
                                            :min=1
                                            :max=10
                                            :interval=1
                                            :piecewise=true
                                            ref="sliderSocial"
                                            id="sliderSocial"
                                            :disabled="noAplicaSocial"
                                            v-model="puntajeSocial"
                                    >
                                    </vue-slider>
                                </div>

                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="noAplicaSocial" style="margin-right: 2em; margin-top: 2em">No Aplica / No tengo opinión </label>
                                    <input type="checkbox" id="noAplicaSocial" :value="1" v-model="noAplicaSocial">
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
                        <button class="btn btn-primary pull-right" v-if="!enviado" @click="enviarEvaluacion">
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
        name: "evaluarPersona",
        components: {
            'vue-slider': VueSlider
        },
        props: ['persona', 'prop-respuesta', 'actividad'],
        created: function () {
            //this.persona = JSON.parse(this.propPersona);
            if (this.propRespuesta !== undefined) {
                this.respuestaAnterior = JSON.parse(this.propRespuesta);
                this.puntajeTecnico = this.respuestaAnterior.puntajeTecnico;
                this.puntajeSocial = this.respuestaAnterior.puntajeSocial;
                if (this.puntajeTecnico === null) { this.puntajeTecnico = true; }
                if (this.puntajeSocial === null) { this.puntajeSocial = true; }
                this.comentario = this.respuestaAnterior.comentario;
                this.enviado = true;
            }
        },
        data: function () {
            return {
                puntajeSocial: 5,
                puntajeTecnico: 5,
                //persona: {},
                comentario: '',
                abierto: true,
                noAplicaTecnico: false,
                noAplicaSocial: false,
                error: false,
                enviado: false,
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