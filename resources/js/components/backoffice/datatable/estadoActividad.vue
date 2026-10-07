<template>
    <span>
        <span v-if="estadoInscripcion === 'abiertas'" class="label label-info" role="alert" >
            {{ $t('backend.open_registrations') }}
        </span>
        <span v-else-if="estadoInscripcion === 'proximas'" class="label label-default" role="label" >
            {{ $t('backend.registrations_not_open_yet') }}
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
// Los estados son COMPUTED sobre rowData: vuetable-2 reutiliza el componente de cada celda por
// posición (:key="itemIndex"), así que al paginar/filtrar/ordenar cambia rowData pero no se
// vuelve a montar. Antes se calculaban una sola vez en mounted() y la etiqueta quedaba
// "congelada" con el estado de la fila que ocupaba esa posición en la primera carga (reclamo #8).
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
    computed: {
        // 'abiertas' | 'proximas' | 'cerradas'. Mismo criterio que el server
        // (Actividad::inscripcionesAbiertas): una fecha NULL no restringe.
        estadoInscripcion() {
            if (this.rowData.estadoConstruccion !== 'Abierta') {
                return 'cerradas';
            }
            let ahora = moment();
            let inicio = this.rowData.fechaInicioInscripciones;
            let fin = this.rowData.fechaFinInscripciones;
            if (inicio && ahora.isBefore(moment(inicio))) {
                return 'proximas';
            }
            if (fin && ahora.isAfter(moment(fin))) {
                return 'cerradas';
            }
            return 'abiertas';
        },
        estadoEvaluaciones() {
            let inicio = this.rowData.fechaInicioEvaluaciones;
            let fin = this.rowData.fechaFinEvaluaciones;
            if (!inicio || !fin) {
                return false;
            }
            return moment().isBetween(moment(inicio), moment(fin));
        },
        // "vencido" = HOY ya es un día POSTERIOR a la fecha límite de pago. La
        // fecha límite es INCLUSIVA del día cargado: el pago vale durante todo ese
        // día (la hora no se persiste), así que se compara por DÍA. Mismo criterio
        // que el server (Actividad::pagoFueraDeFecha).
        estadoPago() {
            if (!this.rowData.fechaLimitePago) {
                return false;
            }
            let limite = moment(moment(this.rowData.fechaLimitePago).format('YYYY-MM-DD'), 'YYYY-MM-DD');
            return limite.isBefore(moment().startOf('day'));
        }
    }
}
</script>
