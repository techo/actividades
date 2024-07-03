<template>
     <div class="col-sm-4">
         <simplert ref="confirmar"></simplert>
        <div class="card tarjeta p-3"  v-bind:style="{backgroundColor:inscripcion.tipoColor}"> 
            <img class="card-img-top" v-on:click="ir_a_actividad" :src="inscripcion.img"
                 alt="Card image cap">
            <div class="card-body px-0">
                <p class="techo-titulo-card">{{ inscripcion.tipo.nombre }}</p>
                <h5 class="card-title text-left" v-on:click="ir_a_actividad">{{ inscripcion.nombreActividad }}</h5>
                <div>
                    <hr>
                    <span v-if="inscripcion.show_dates" class="small-text"><i class="fas fa-calendar-alt"></i> <span style="padding-bottom: 5px">{{ inscripcion.fecha }}</span></span>
                    <span v-if="inscripcion.show_dates" class="small-text"><i class="fas fa-clock"></i> {{ inscripcion.hora }}</span>
                    <span v-if="inscripcion.show_location" class="small-text"><i class="fas fa-map-marker-alt"></i> {{ inscripcion.lugar }}</span>
                    <hr>
                </div>
                <p class="card-text text-left">{{ inscripcion.descripcion | truncate(120) }}</p>
                <div>
                    <span v-if="!actividadPasada">
                        <a
                            class="btn btn-danger text-light font-weight-bold pull-right"
                            @click="desincribir(inscripcion.idActividad)"
                        >
                            {{ $t('frontend.unapply') }}
                        </a>
                    </span>
                    <span v-else> <!-- la actividad ya terminó -->
                        <span v-if="periodoDeEvaluacionYaComenzo">
                            <a 
                                    class="btn btn-info text-light font-weight-bold pull-right"
                                    v-show="inscripcion.presente === 1"
                                    @click="ir_a_evaluar"
                            >
                            {{ $t('frontend.view_evaluations') }}
                            </a>
                            <p v-show="inscripcion.presente === 0">
                                <strong> {{ $t('frontend.absent') }}</strong>
                            </p>
                        </span>
                        <span v-else>  <!-- Actividad terminó pero no esta en el periodo de evaluación -->
                            <p><strong> {{ $t('frontend.evaluations_start_on') }} <br>{{ inscripcion.fechaInicioEvaluaciones}}</strong></p>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'tarjeta',
        props: ['inscripcion'],
        data () {
            return {
                key: ''
            }
        },

        filters: {
            truncate: function(string, value) {
                if(!string) return '';
                return string.substr(0,value) + '...';
            },

            ubicacion: function (ubicacion) {
                return ubicacion === null ? "" : ubicacion.localidad;
            }
        },
        methods: {
            ir_a_evaluar: function () {
                window.location.href = '/actividades/' + this.inscripcion.idActividad + '/evaluaciones'
            },
            ir_a_actividad: function () {
                window.location.href = '/actividades/' + this.inscripcion.idActividad
            },
            desincribir: function (idActividad) {
                let self = this;
                self.$refs.confirmar.openSimplert({
                    title: this._i18n.t('frontend.unenroll_title'),
                    message: this._i18n.t('frontend.message_1') + self.inscripcion.nombreActividad + this._i18n.t('frontend.message_2'),
                    useConfirmBtn: true,
                    isShown: true,
                    disableOverlayClick: true,
                    customClass: 'confirmar',
                    customCloseBtnText: this._i18n.t('frontend.message_1'), //string -- close button text
                    customCloseBtnClass: 'btn btn-default', //string -- custom class for close button
                    customConfirmBtnText: this._i18n.t('frontend.unenroll_button'), //string -- confirm button text
                    customConfirmBtnClass: 'btn btn-danger mb-1', //string -- custom class for confirm button
                    onConfirm: function() {
                        axios.delete('/ajax/usuario/inscripciones/' + idActividad).then(response => {
                            self.$parent.traer_inscripciones();
                            self.$parent.borro = true;
                            setTimeout(function(){
                                self.$parent.borro = false;
                            }, 3000)
                        })
                    }
                })
            }
        },
        computed: {
            actividadPasada: function () {

                let fechaFin = moment(this.inscripcion.fechaFin,"DD-MM-YYYY hh:mm");
                //debugger;

                if (fechaFin === null || fechaFin === undefined) {
                    return false;
                }

                if (fechaFin  < Date.now()) {
                    return true;
                }
            },
            periodoDeEvaluacionYaComenzo: function () {
                let fechaInicioEvaluaciones = new Date(
                    this.inscripcion.fechaInicioEvaluaciones.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3")
                ).getTime();

                let ahora = new Date().getTime();

                return (ahora > fechaInicioEvaluaciones)
            }
        }
    }
</script>
<style>

div.tarjeta {
    cursor: pointer;
    border: 1;
    border-radius: 8%;
    text-align: center;
}

.confirmar > div {
    min-width: 60%;
}
.confirmar .simplert__icon {
    display: none;
}

    .small-text {
        font-size: smaller;
        margin-right: 10px;
        text-align: left;
    }
</style>