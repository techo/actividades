<template>
    <div>
        <div class="accordion" id="evaluaciones">
            <div class="card"  style="width: 100%">
                <div class="card-header" id="headingOne">
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
                        <div class="form-group">
                            <label for="slider">Puntaje Social</label>
                            <vue-slider
                                    :min=1
                                    :max=10
                                    :interval=1
                                    ref="slider"
                                    id="slider"
                                    v-model="puntaje"
                            >
                            </vue-slider>
                        </div>
                        <div class="form-group">
                            <label for="comentarios">Comentarios</label>
                            <textarea name="comentarios" id="comentarios" cols="30" rows="5" class="form-control">
                                {{ comentarios }}
                            </textarea>
                        </div>
                        <button class="btn btn-primary pull-right" v-if="!enviado">
                            Enviar Evaluación
                        </button>
                        <p class="pull-right" v-else><strong>¡Gracias por tu opinión!</strong></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VueSlider from 'vue-slider-component'; //https://github.com/NightCatSama/vue-slider-component
    import axios from 'axios';
    export default {
        name: "evaluarActividad",
        components: {
            'vue-slider': VueSlider
        },
        props: ['prop-actividad'],
        created: function () {
          this.actividad = JSON.parse(this.propActividad);
        },
        data: function () {
            return {
                puntaje: 5,
                actividad: {},
                comentarios: '',
                enviado: false,
                abierto: true
            }
        },
        methods: {
            enviarEvaluacion: function () {
                this.abierto = !this.abierto;
                // let url = '/actividades/' + this.actividad.idActividad + '/evaluaciones';
                // this.axiosPost(url, function(response, self) {
                //
                // }, payload);
            },
            axiosPost(url, fCallback, params = []) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                axios.post(url, params)
                    .then(response => {
                        fCallback(response.data, this);
                        Event.$emit('success');
                        this.readonly = true;
                    })
                    .catch((error) => {
                        Event.$emit('error');
                        // Error
                        console.info('Error en: ' + url);
                        console.error(error.response.status);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            // console.error(error.response.data);
                            // console.error(error.response.status);
                            // console.error(error.response.headers);
                            if (error.response.status === 422) {
                                // debugger;
                                this.validationErrors = Object.values(error.response.data);
                            }
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.error(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });

            },
        }
    }
</script>

<style scoped>
    .collapse {
        border-bottom: solid thin #d9dde2;
        padding-bottom: 2em;
    }

    .btn-link {

    }
</style>