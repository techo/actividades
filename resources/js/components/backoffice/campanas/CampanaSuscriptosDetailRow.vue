<template>
    <tr>
        <td colspan="6" style="background:#f9f9f9; padding: 12px 20px;">
            <div v-if="rowData.respuestas && rowData.respuestas.length">
                <strong>{{ $t('backend.campaign_questions') }}</strong>
                <table class="table table-condensed table-bordered" style="margin-top:8px; margin-bottom:0; background:#fff;">
                    <thead>
                        <tr>
                            <th>{{ $t('backend.pregunta') }}</th>
                            <th>{{ $t('backend.answer') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="respuesta in rowData.respuestas" :key="respuesta.id">
                            <td>{{ respuesta.pregunta ? respuesta.pregunta.pregunta : '-' }}</td>
                            <td>{{ respuesta.respuesta || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-muted">
                {{ $t('backend.no_answers') }}
            </div>
        </td>
    </tr>
</template>

<script>
export default {
    name: 'campana-suscriptos-detail-row',
    props: {
        rowData: {
            type: Object,
            required: true,
        },
        rowIndex: {
            type: Number,
        },
    },
}
</script>
