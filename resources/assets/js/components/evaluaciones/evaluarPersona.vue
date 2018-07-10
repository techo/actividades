<template>
    <div class="col-md-8">
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
                            Evaluación de {{ nombre }} - {{ persona.rol }}
                            <span v-show="abierto" class="pull-right"><i class="fa fa-chevron-up"></i></span>
                            <span v-show="!abierto" class="pull-right"><i class="fa fa-chevron-down"></i></span>
                        </h6>
                    </span>
                </div>

                <div
                        :id="'cardEvaluacionPersona_'+ persona.idPersona"
                        class="collapse show"
                        :data-parent="'#evaluacionPersona_' + persona.idPersona"
                >
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-7">
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
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="noAplicaTecnico" style="margin-right: 2em; margin-top: 2em">No Aplica / No tengo opinión </label>
                                    <input type="checkbox" id="noAplicaTecnico" :value="1" v-model="noAplicaTecnico">
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-7">
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
                            <div class="col-md-5">
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

                        <button
                                class="btn btn-primary pull-right"
                                v-if="!enviado && !evaluacionPasada"
                                @click="enviarEvaluacion"
                        >
                            Enviar Evaluación
                        </button>
                        <p class="pull-right" v-if="enviado"><strong>¡Gracias por tu opinión!</strong></p>
                        <p class="pull-right" v-if="evaluacionPasada">La fecha de fin de las evaluaciones ya pasó &#9785;</p>
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
        props: ['persona', 'actividad'],
        created: function () {
            // Si puntaje Social existe, puntaje tecnico también
            if (this.persona.puntajeSocial !== undefined) {
                this.puntajeTecnico = this.persona.puntajeTecnico;
                this.puntajeSocial = this.persona.puntajeSocial;
                if (this.puntajeTecnico === null) { this.noAplicaTecnico = true; }
                if (this.puntajeSocial === null) { this.noAplicaSocial = true; }
                this.comentario = this.persona.comentario;
                this.enviado = true;
            }
        },
        data: function () {
            return {
                puntajeSocial: 5,
                puntajeTecnico: 5,
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
                    comentario: this.comentario,
                    evaluado: this.persona,
                    noAplicaSocial: this.noAplicaSocial,
                    noAplicaTecnico: this.noAplicaTecnico
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
</style>