<template>
    <span class="celda-whatsapp">
        <a v-if="link" :href="link" target="_blank" :title="rowData.telefonoMovil">
            <i class="fa fa-whatsapp" aria-hidden="true"></i> {{ rowData.telefonoMovil }}
        </a>
        <span v-else class="text-muted">—</span>
    </span>
</template>

<script>
export default {
    name: 'celda-whatsapp',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
        fieldDef: { type: Object },
    },
    computed: {
        link() {
            const tel = this.rowData.telefonoMovil
            if (!tel) return null
            // wa.me requiere solo dígitos (sin +, espacios ni separadores).
            const digitos = String(tel).replace(/\D/g, '')
            return digitos ? 'https://wa.me/' + digitos : null
        },
    },
}
</script>

<style scoped>
.celda-whatsapp .fa-whatsapp {
    color: #25d366;
}
</style>
