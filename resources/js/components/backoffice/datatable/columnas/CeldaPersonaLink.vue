<template>
    <a v-if="rowData.idPersona" :href="'/admin/usuarios/' + rowData.idPersona" target="_blank"
       :title="$t('backend.view_profile')">{{ valor }}</a>
    <span v-else>{{ valor }}</span>
</template>

<script>
/**
 * Nombre/apellido del inscripto como link al perfil (historial, contacto). Reemplaza
 * el "Ver perfil" del viejo detalle inline que se quitó con las columnas configurables.
 * El atributo a mostrar viene en fieldDef.atributo (config/datatables.php, fijas).
 */
export default {
    name: 'celda-persona-link',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
        fieldDef: { type: Object },
    },
    computed: {
        valor() {
            const atributo = this.fieldDef && this.fieldDef.atributo
            return atributo ? this.rowData[atributo] : ''
        },
    },
}
</script>
