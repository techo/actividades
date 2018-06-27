<template>
     <div class="col-sm-4">
        <div class="card tarjeta p-3">
            <simplert ref="confirmar"></simplert>
            <img class="card-img-top" v-on:click="ir_a_actividad" :src="inscripcion.tipo.imagen"
                 alt="Card image cap">
            <div class="card-body px-0">
                <p class="techo-titulo-card">{{ inscripcion.tipo.nombre }}</p>
                <h5 class="card-title text-left" v-on:click="ir_a_actividad">{{ inscripcion.nombreActividad }}</h5>
                <div>
                    <hr>
                    <span class="col-sm-4"><i class="fas fa-calendar-alt"></i> <span style="padding-bottom: 5px">{{ inscripcion.fecha }}</span></span>
                    <span class="col-sm-4"><i class="fas fa-clock"></i> {{ inscripcion.hora }}</span>
                    <span class="col-sm-4"><i class="fas fa-map-marker-alt"></i> {{ inscripcion.localidad | ubicacion }}</span>
                    <hr>
                </div>
                <p class="card-text text-left">{{ inscripcion.descripcion | truncate(120) }}</p>
                <div>
                    <span v-if="!actividadPasada">
                        <a class="btn btn-success text-light font-weight-bold pull-right" @click="desincribir(inscripcion.idActividad)">Desinscribirme</a>
                    </span>
                    <span v-else>
                        <a class="btn btn-info text-light font-weight-bold pull-right" @click="ir_a_evaluar">Evaluar</a>
                    </span>


                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import moment from 'moment';
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
                var self = this;
                self.$refs.confirmar.openSimplert({
                    title:'DESINSCRIBIRME DE ACTIVIDAD',
                    message:"Estás por desinscribirte de la actividad " + self.inscripcion.actividad.nombreActividad + ", se borrarán tus datos para participar. Puedes inscribirte cuando desees. ¿Deseas continuar?",
                    useConfirmBtn: true,
                    isShown: true,
                    disableOverlayClick: true,
                    customClass: 'confirmar',
                    customCloseBtnText: 'CANCELAR', //string -- close button text
                    customCloseBtnClass: '', //string -- custom class for close button
                    customConfirmBtnText: 'SI, DESINSCRIBIRME', //string -- confirm button text
                    customConfirmBtnClass: '', //string -- custom class for confirm button
                    onConfirm: function() {
                        axios.delete('/ajax/usuario/inscripciones/' + idActividad).then(response => {
                            self.$parent.traer_inscripciones()
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
            hoy: function() { return Date.now();
                return moment();
            },
            actividadPasada: function () {
                let fechaFin = new Date(this.inscripcion.fechaFin.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3")).getTime();

                if (fechaFin === null || fechaFin === undefined) {
                    return false;
                }

                if (fechaFin  < Date.now()) {
                    return true;
                }
            }
        }
    }
</script>
<style>

div.tarjeta {
    cursor: pointer;
    border: 0px;
    text-align: center;
}

.confirmar > div {
    min-width: 60%;
}
.confirmar .simplert__icon {
    display: none;
}

</style>