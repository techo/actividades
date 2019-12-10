<template>
    <div class="contenedor">
        <simplert ref="confirmar"></simplert>
        <button
                v-if="actividadPasada && periodoDeEvaluacionYaComenzo && inscripcionPresente"
                class="btn btn-sm btn-info"
                @click="ir_a_evaluar"
        >
            {{ __('frontend.view_evaluations') }}
        </button>
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
                idActividad: this.rowData.idActividad
            }
        },
        methods: {
            ir_a_evaluar: function () {
                window.location.href = '/actividades/' + this.idActividad + '/evaluaciones'
            },
        },
        computed: {
            actividadPasada: function () {
                return moment().isAfter(moment(this.rowData.fechaFin, "DD/MM/YYYY hh:mm:ss"));
            },
            periodoDeEvaluacionYaComenzo: function () {
                return moment().isSameOrAfter(moment(this.rowData.fechaInicioEvaluaciones, "DD/MM/YYYY hh:mm:ss")) &&
                    moment().isSameOrBefore(moment(this.rowData.fechaFinEvaluaciones, "DD/MM/YYYY hh:mm:ss"))
            },
            inscripcionPresente: function () {
                return this.rowData.presente;
            }
        },
        watch: {
            // Para Mantener sincronizado el valor en casos en que el datatable filtra y el prop cambia
            'rowData.idActividad': function (nuevoValor) {
                this.idActividad = nuevoValor;
            }
        }
    }
</script>

<style scoped>
 .small-caps {
     font-size: smaller;
 }
    .contenedor {
        margin-top: 30px
    }
</style>
