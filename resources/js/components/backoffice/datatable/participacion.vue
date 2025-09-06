<template>
    <span>
        <span
            v-if="rowData.participacion_status"
            :class="['label', statusClass]"
        >
            {{ $t('backend.' + rowData.participacion_status) }}
        </span>
    </span>
</template>

<script>
export default {
    name: "tag_participacion",
    props: {
        rowData: {
            type: Object,
            required: true
        }
    },
    computed: {
        statusClass() {
            switch (this.rowData.participacion_status) {
                case 'onfire':
                    return 'label-danger'; // rojo
                case 'comprometido':
                    return 'label-success'; // verde
                case 'reactivar':
                    return 'label-warning'; // amarillo
                case 'sin_senial':
                    return 'label-default'; // gris
                default:
                    return 'label-default';
            }
        }
    }
}
</script>
