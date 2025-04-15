<template>
    <span>
        <span v-if="estadoInscripcion && (rowData.estadoConstruccion == $t('backend.open'))" class="label label-info" role="alert" >
            {{ $t('backend.open_registrations') }}
        </span>
        <span v-else class="label label-default" role="label" >
            {{ $t('backend.closed_registrations') }}
        </span>

        <span v-if="estadoEvaluaciones" class="label label-warning" role="alert" >
            {{ $t('backend.open_evaluations') }}
        </span>

        <span v-if="(estadoPago && rowData.pago)" class="label label-danger" role="alert" >
            {{ $t('backend.payment_date_expired') }}
        </span>
    </span>
</template>

<script>
export default {
    name: "tag_estado_actividad",
    props: {
        rowData: {
            type: Object,
            required: true
        },
        rowIndex: {
            type: Number
        }
    },
    data() {
        return {
            fechas: {
                fechaInicio: null,
                fechaFin: null,

                fechaInicioInscripciones: null,
                fechaFinInscripciones: null,

                fechaInicioEvaluaciones: null,
                fechaFinEvaluaciones: null,

                fechaLimitePago: null,
            },
            horas: {
                fechaInicio: null,
                fechaFin: null,

                fechaInicioInscripciones: null,
                fechaFinInscripciones: null,

                fechaInicioEvaluaciones: null,
                fechaFinEvaluaciones: null,

                fechaLimitePago: null,
            },
            estadoInscripcion: false,
            estadoEvaluaciones: false,
            estadoPago: false
        }
    },

    mounted() {
        this.cargarFechas();
    },
    methods: {
        cargarFechas(){
            this.fechas.fechaInicio = moment(this.rowData.fechaInicio).format('YYYY-MM-DD');
            this.horas.fechaInicio = moment(this.rowData.fechaInicio).format('HH:mm:ss');
            this.fechas.fechaFin = moment(this.rowData.fechaFin).format('YYYY-MM-DD');
            this.horas.fechaFin = moment(this.rowData.fechaFin).format('HH:mm:ss');

            this.fechas.fechaInicioInscripciones = moment(this.rowData.fechaInicioInscripciones).format('YYYY-MM-DD');
            this.horas.fechaInicioInscripciones = moment(this.rowData.fechaInicioInscripciones).format('HH:mm:ss');

            this.fechas.fechaFinInscripciones = moment(this.rowData.fechaFinInscripciones).format('YYYY-MM-DD');
            this.horas.fechaFinInscripciones = moment(this.rowData.fechaFinInscripciones).format('HH:mm:ss');

            this.fechas.fechaInicioEvaluaciones = moment(this.rowData.fechaInicioEvaluaciones).format('YYYY-MM-DD');
            this.horas.fechaInicioEvaluaciones = moment(this.rowData.fechaInicioEvaluaciones).format('HH:mm:ss');

            this.fechas.fechaFinEvaluaciones = moment(this.rowData.fechaFinEvaluaciones).format('YYYY-MM-DD');
            this.horas.fechaFinEvaluaciones = moment(this.rowData.fechaFinEvaluaciones).format('HH:mm:ss');

            this.fechas.fechaLimitePago = moment(this.rowData.fechaLimitePago).format('YYYY-MM-DD');
            this.horas.fechaLimitePago = moment(this.rowData.fechaLimitePago).format('HH:mm:ss');

            this.estadoInscripcion = moment().isBetween(
                this.fechas.fechaInicioInscripciones +' '+ this.horas.fechaInicioInscripciones,
                this.fechas.fechaFinInscripciones +' '+ this.horas.fechaFinInscripciones
                );

            this.estadoEvaluaciones = moment().isBetween(
                this.fechas.fechaInicioEvaluaciones +' '+ this.horas.fechaInicioEvaluaciones,
                this.fechas.fechaFinEvaluaciones +' '+ this.horas.fechaFinEvaluaciones
                );

            this.estadoPago = moment().isBefore(
                this.fechas.fechaLimitePago,
                );
        }
    }
}
</script>
