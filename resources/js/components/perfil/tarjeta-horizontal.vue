<template>
    <div class="row">
        <div class="col-md-3">
           <img :src="rowData.img" alt="rowData.tipo" width="120">
        </div>
        <div class="col-md-9">
            <div class="small-caps">
                {{ rowData.tipo }}
            </div>
            <div><a :href="urlDetalle">{{ rowData.nombreActividad }}</a></div>

            <span v-if="rowData.show_dates" class="small-text"><i class="fas fa-calendar-alt"></i> {{ rowData.fecha }}</span>
            <span v-if="rowData.show_dates" class="small-text"><i class="fas fa-clock"></i> {{ rowData.hora }}</span>
            <span v-if="rowData.show_location" class="small-text"><i class="fas fa-map-marker-alt"></i> {{ rowData.lugar }}</span>
            <button
                v-if="actividadPasada && periodoDeEvaluacionYaComenzo && inscripcionPresente"
                class="btn btn-sm btn-info"
                @click="ir_a_evaluar"
        >
            {{ $t('frontend.view_evaluations') }}
        </button>

        </div>
    </div>
</template>

<script>
export default {
    name: "tarjeta-horizontal",
    props: {
        rowData: {
            type: Object,
            required: true
        },
        rowIndex: {
            type: Number
        }
    },
    data: function () {
        return {
            idActividad: this.rowData.idActividad,
            //urlDetalle: '/actividades/' + this.rowData.idActividad
        }
    },
    watch: {
        'rowData.idActividad': function(nuevo) {
            this.idActividad = nuevo;
        }
    },
    methods: {
        ir_a_evaluar: function () {
                window.location.href = '/actividades/' + this.rowData.idActividad + '/evaluaciones'
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
        },
        urlDetalle: function () {
            return '/actividades/' + this.idActividad;
        }
    }
}
</script>

<style scoped>
    .small-caps {
        font-size: smaller;
        font-variant: all-petite-caps;
    }
    .small-text {
        font-size: smaller;
    }

    span.small-text {
        margin-right: 10px;
    }
</style>