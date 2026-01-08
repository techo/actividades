<template>
    <div>
        <h4>3 . {{ $t('evaluacion.impacto.titulo') }}</h4>

        <div v-for="(pregunta, keyPregunta) in preguntasImpacto"
             :key="keyPregunta"
             class="mb-4"
        >
            <h5>
                {{ pregunta.title }}
            </h5>

            <div
                class="infoPuntaje"
                :style="{ backgroundColor: colorPuntaje(puntajesImpacto[keyPregunta]) }"
            >
                {{ puntajesImpacto[keyPregunta] }}
            </div>

            <input
                type="range"
                class="form-control-range"
                :id="'slider-' + keyPregunta"
                min="1"
                max="10"
                step="1"
                v-model="puntajesImpacto[keyPregunta]"
                :disabled="evaluacionPasada || evaluacionRealizada"
            >
        </div>

        <button
            class="btn btn-primary pull-right"
            v-if="!enviado && !evaluacionPasada && !evaluacionRealizada"
            @click="submit"
        >
            {{ $t('frontend.send') }}
        </button>

        <p class="pull-right" v-if="enviado || evaluacionRealizada">
            <strong>{{ $t('frontend.sent') }}</strong>
        </p>

        <p class="pull-right" v-if="evaluacionPasada && !enviado">
            {{ $t('frontend.feedback_date_expired') }}
        </p>

        <p class="red" v-if="error">
            <i class="fas fa-exclamation"></i>
            <i style="margin-left: 0.5em">
                {{ $t('frontend.unable_to_sent_feeback') }}
            </i>
        </p>
    </div>
</template>

<script>
export default {
    name: 'evaluarImpacto',
    props: {
        evaluacionRespuesta: {
            type: Object,
            required: true
        },
        actividad: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            preguntasImpacto: {},
            puntajesImpacto: {},
            enviado: false,
            error: false
        }
    },
    computed: {
        evaluacionPasada() {
            let fechaFinEvaluacion = new Date(this.actividad.fechaFinEvaluacion);
            let ahora = new Date();
            return ahora > fechaFinEvaluacion;
        },
        evaluacionRealizada()  {
            return (
                this.evaluacionRespuesta &&
                Object.keys(this.evaluacionRespuesta).length > 0
            )
        }
    },
    created() {
        this.preguntasImpacto = this.$t('evaluacion.impacto.preguntas') || {}

        Object.keys(this.preguntasImpacto).forEach(key => {
            this.$set(this.puntajesImpacto, key, 9)

            if (this.evaluacionRealizada && this.evaluacionRespuesta[key] != null) {
                this.puntajesImpacto[key] = this.evaluacionRespuesta[key]
            }
        })
    },
    methods: {
        submit() {
            let url = '/actividades/' + this.actividad.idActividad + '/evaluaciones/impacto';
            let payload = {
                idActividad: this.actividad.idActividad,
                impacto_habilidades_capacidades: this.puntajesImpacto.impacto_habilidades_capacidades,
                impacto_percepcion_realidad: this.puntajesImpacto.impacto_percepcion_realidad,
                impacto_recomendaria_experiencia: this.puntajesImpacto.impacto_recomendaria_experiencia,
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
        colorPuntaje(v) {
            if (v <= 3) return "#F7977A"
            if (v <= 6) return "#FFF79A"
            return "#82CA9D"
        }
    }
}
</script>

<style scoped>
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
}
</style>
