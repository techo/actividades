<template>
            <div class="card"  style="">

                <div id="cardEvaluacion" class="collapse show" aria-labelledby="headingOne">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p class="text-muted">
                                    {{ $t('frontend.my_score_description') }}
                                </p>
                                <div class="form-group">
                                    <label for="slider">{{ $t('frontend.technical_score') }}
                                    <div class="infoPuntaje text-center" :style="{ 'background-color': colorPuntajeTecnico}">{{ puntajeTecnico }}</div></label>
                                    <input type="range"
                                           class="form-control-range"
                                           id="slider"
                                           min="1"  
                                           max="10"
                                           step="1"
                                           :disabled="true"
                                           v-model="puntajeTecnico">
                                </div>
                                <div class="form-group">
                                    <label for="slider">{{ $t('frontend.social_score') }}
                                    <div class="infoPuntaje text-center" :style="{ 'background-color': colorPuntajeSocial}">{{ puntajeSocial }}</div></label>
                                    <input type="range"
                                           class="form-control-range"
                                           id="slider"
                                           min="1"
                                           max="10"
                                           step="1"
                                           :disabled="true"
                                           v-model="puntajeSocial">
                                </div>
                                <div class="form-group">
                                    <label for="slider">{{ $t('frontend.gender_score') }}
                                    <div class="infoPuntaje text-center" :style="{ 'background-color': colorPuntajeGenero}">{{ puntajeGenero }}</div></label>
                                    <input type="range"
                                           class="form-control-range"
                                           id="slider"
                                           min="1"
                                           max="10"
                                           step="1"
                                           :disabled="true"
                                           v-model="puntajeGenero">
                                </div>
                                <p class="text-muted">
                                    {{ $t('frontend.my_score_contact') }}
                                </p>
                                <a href="/perfil/actividades" class="btn btn-primary" >{{ $t('frontend.my_score_feedback') }}</a>

                            </div>

                        </div>
                    </div>

                </div>
            </div>
</template>

<script>
    import VueSlider from 'vue-slider-component'; //https://github.com/NightCatSama/vue-slider-component
    export default {
        name: "miEvaluacion",
        components: {
            'vue-slider': VueSlider
        },
        props: ['puntajeTecnico', 'puntajeSocial', 'puntajeGenero'],
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
        }
    }
</script>

<style scoped>
    .subtitle {
        margin: 25px 0;
    }
    .collapse {
        border-bottom: solid thin #d9dde2;
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