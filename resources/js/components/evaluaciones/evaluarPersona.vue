<template>
    <div class="col-md-8">
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
                            {{ $t('frontend.feedback_of') }} {{ nombre }} - {{ persona.rol }}
                            <span v-show="abierto" class="pull-right"><i class="fa fa-chevron-up"></i></span>
                            <span v-show="!abierto" class="pull-right"><i class="fa fa-chevron-down"></i></span>
                        </h6>
                    </div>
                </div>

                <div
                        :id="'cardEvaluacionPersona_'+ persona.idPersona"
                        class="collapse"
                        :data-parent="'#evaluacionPersona_' + persona.idPersona"
                >
                    <div class="card-body">
                        <div class="row">
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