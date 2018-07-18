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
        <span v-if="actividadPasada && !periodoDeEvaluacionYaComenzo" class="small-caps">
            El período de evaluaciones comienza el <br>
            {{ this.rowData.fechaInicioEvaluaciones }}
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
                idActividad: ''
            }
        },
        methods: {
            ir_a_evaluar: function () {
                window.location.href = '/actividades/' + this.idActividad + '/evaluaciones'
            },
        },
        computed: {
            actividadPasada: function () {
                let fechaFin = new Date(this.rowData.fechaFin.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3")).getTime();

                return (fechaFin < Date.now());
            },
            periodoDeEvaluacionYaComenzo: function () {
                let fechaInicioEvaluaciones = new Date(
                    this.rowData.fechaInicioEvaluaciones.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3")
                ).getTime();

                let ahora = new Date().getTime();

                return (ahora > fechaInicioEvaluaciones)
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