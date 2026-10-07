<template>
    <div class="reporte-acciones">
        <button class="btn btn-xs btn-default" @click="ver" title="Ver detalle">
            <i class="fa fa-eye"></i>
        </button>
        <a v-if="rowData.has_screenshot" class="btn btn-xs btn-default" :href="rowData.screenshot_url" target="_blank" title="Ver captura">
            <i class="fa fa-image"></i>
        </a>
    </div>
</template>

<script>
export default {
    name: 'reporte-acciones',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
    },
    methods: {
        ver() { Event.$emit('reporte:ver', this.rowData); },
    },
};
</script>

<style scoped>
.reporte-acciones { white-space: nowrap; }
.reporte-acciones .btn { margin-right: 2px; }
</style>
