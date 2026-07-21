<template>
    <span class="celda-nivel">
        <span class="label" :class="tier.clase" :title="$t('backend.participations') + ': ' + participaciones">
            {{ $t(tier.label) }}
        </span>
    </span>
</template>

<script>
export default {
    name: 'celda-nivel',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
        fieldDef: { type: Object },
    },
    computed: {
        // Cantidad de actividades presentes (inyectada por EnriquecedorFilas).
        participaciones() {
            return Number(this.rowData.participaciones) || 0
        },
        // Rookie: 0-2 · Champion/Staff-ready: 3-6 · Guardian: 7+
        tier() {
            const n = this.participaciones
            if (n >= 7) return { label: 'backend.level_guardian', clase: 'label-success' }
            if (n >= 3) return { label: 'backend.level_champion', clase: 'label-primary' }
            return { label: 'backend.level_rookie', clase: 'label-default' }
        },
    },
}
</script>

<style scoped>
.celda-nivel .label {
    font-size: .85em;
}
</style>
