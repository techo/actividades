<template>
    <div>
        <simplert ref="confirmar"></simplert>
        <button v-if="!actividadPasada" type="button" class="btn btn-sm btn-warning">Desinscribirse</button>
        <button v-if="actividadPasada && periodoDeEvaluacionYaComenzo" class="btn btn-sm btn-info">Ver Evaluaciones</button>
        <span v-if="actividadPasada && !periodoDeEvaluacionYaComenzo" class="small-text">
            El período de evaluaciones comienza el <br>{{ this.rowData.fechaInicioEvaluaciones }}
        </span>
    </div>
</template>

<script>
    export default {
        name: "btnMisActividades",
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                type: Number
            }
        },
        data: function() {
            return {

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
            actividadPasada: function () {
                let fechaFin = new Date(this.rowData.fechaFin.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3")).getTime();

                if (fechaFin  < Date.now()) {
                    return true;
                }
                return false;
            },
            periodoDeEvaluacionYaComenzo: function () {
                let fechaInicioEvaluaciones = new Date(
                    this.rowData.fechaInicioEvaluaciones.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3")
                ).getTime();

                let ahora = new Date().getTime();

                return (ahora > fechaInicioEvaluaciones)
            }
        }
    }
</script>

<style scoped>
 .small-text {
     font-size: smaller;
 }
</style>