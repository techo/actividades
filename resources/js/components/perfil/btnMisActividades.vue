<template>
    <div class="contenedor">
        <simplert ref="confirmar"></simplert>
        <button
                v-if="actividadPasada && periodoDeEvaluacionYaComenzo"
                class="btn btn-sm btn-info"
                @click="ir_a_evaluar"
        >
            Ver Evaluaciones
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
                let aver = moment().isAfter(moment(this.rowData.fechaFin, "DD/MM/YYYY mm:ss"));
                //debugger;
                return moment().isAfter(moment(this.rowData.fechaFin, "DD/MM/YYYY mm:ss"));
            },
            periodoDeEvaluacionYaComenzo: function () {
                return moment().isAfter(moment(this.rowData.fechaInicioEvaluaciones, "DD/MM/YYYY mm:ss")) &&
                    moment().isBefore(moment(this.rowData.fechaFinEvaluaciones, "DD/MM/YYYY mm:ss"))
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
