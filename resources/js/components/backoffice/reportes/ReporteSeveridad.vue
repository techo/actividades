<template>
    <span v-if="rowData.severity" class="label" :class="clase">{{ texto }}</span>
    <span v-else class="text-muted">—</span>
</template>

<script>
const TEXTOS = { low: 'Baja', medium: 'Media', high: 'Alta', critical: 'Crítica' };
const CLASES = { low: 'label-default', medium: 'label-primary', high: 'label-warning', critical: 'label-danger' };

export default {
    name: 'reporte-severidad',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
    },
    computed: {
        texto() { return TEXTOS[this.rowData.severity] || this.rowData.severity; },
        clase() { return CLASES[this.rowData.severity] || 'label-default'; },
    },
};
</script>
