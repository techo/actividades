<template>
    <span class="label" :class="rowData.type === 'bug' ? 'label-danger' : 'label-info'">
        <i class="fa" :class="rowData.type === 'bug' ? 'fa-bug' : 'fa-lightbulb-o'"></i>
        {{ rowData.type === 'bug' ? 'Bug' : 'Sugerencia' }}
    </span>
</template>

<script>
export default {
    name: 'reporte-tipo',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
    },
};
</script>
