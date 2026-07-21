<template>
    <div class="celda-beca">
        <template v-if="rowData.scholarship_requested">
            <span class="label label-info">{{ $t('backend.requested') }}</span>
            <a v-if="rowData.scholarship_evidence_url"
               :href="'/' + rowData.scholarship_evidence_url"
               target="_blank"
               class="celda-beca__evidencia">
                <i class="fa fa-paperclip"></i> {{ $t('backend.view_evidence') }}
            </a>
            <div v-if="rowData.scholarship_reason" class="celda-beca__motivo" :title="rowData.scholarship_reason">
                {{ rowData.scholarship_reason }}
            </div>
        </template>
        <span v-else class="text-muted">—</span>
    </div>
</template>

<script>
export default {
    name: 'celda-beca',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
        fieldDef: { type: Object },
    },
}
</script>

<style scoped>
.celda-beca {
    min-width: 120px;
}
.celda-beca .label {
    display: inline-block;
}
.celda-beca__evidencia {
    display: inline-block;
    margin-left: 4px;
    white-space: nowrap;
}
.celda-beca__motivo {
    font-size: .8rem;
    color: #666;
    margin-top: 3px;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
