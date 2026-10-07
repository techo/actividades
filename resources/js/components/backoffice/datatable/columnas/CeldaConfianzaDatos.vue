<template>
    <span class="celda-confianza-datos" v-if="data">
        <span class="label" :class="clase" :title="tooltip">{{ $t(labelKey) }}</span>
    </span>
    <span v-else class="text-muted">—</span>
</template>

<script>
/**
 * Muestra la confianza de los datos identitarios de la persona (nivel + tooltip
 * con los motivos). El valor lo inyecta server-side EnriquecedorFilas
 * (App\Services\CalidadDatos\CalidadDatosPersona) en rowData.confianza_datos.
 * Los `motivos` son keys i18n (backend.dq_*) que traducimos acá, con el locale
 * del usuario que mira el backoffice.
 */
export default {
    name: 'celda-confianza-datos',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
        fieldDef: { type: Object },
    },
    computed: {
        data() {
            return this.rowData.confianza_datos || null
        },
        clase() {
            if (!this.data) return 'label-default'
            return {
                ok: 'label-success',
                revisar: 'label-warning',
                critico: 'label-danger',
            }[this.data.nivel] || 'label-default'
        },
        labelKey() {
            if (!this.data) return 'backend.dq_level_revisar'
            return {
                ok: 'backend.dq_level_ok',
                revisar: 'backend.dq_level_revisar',
                critico: 'backend.dq_level_critico',
            }[this.data.nivel] || 'backend.dq_level_revisar'
        },
        tooltip() {
            if (!this.data) return ''
            const cab = this.$t('backend.data_confidence') + ' (' + this.data.puntaje + '/100)'
            const lineas = (this.data.motivos || []).map(k => '• ' + this.$t(k))
            if (this.data.verificado) {
                lineas.push('✓ ' + this.$t('backend.dq_verified_recently'))
            }
            return lineas.length ? cab + '\n' + lineas.join('\n') : cab
        },
    },
}
</script>

<style scoped>
.celda-confianza-datos .label {
    font-size: .85em;
    cursor: help;
}
</style>
