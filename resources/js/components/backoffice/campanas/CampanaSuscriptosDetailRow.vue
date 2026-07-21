<template>
    <tr>
        <td colspan="6" style="background:#f9f9f9; padding: 12px 20px;">

            <!-- Respuestas a preguntas de campaña -->
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
                            <td>
                                <a
                                    v-if="esArchivo(respuesta) && respuesta.respuesta"
                                    :href="urlArchivo(respuesta)"
                                    target="_blank"
                                    rel="noopener"
                                >{{ $t('backend.ver_archivo') }}</a>
                                <span v-else>{{ respuesta.respuesta || '-' }}</span>
                            </td>
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
    methods: {
        esArchivo(respuesta) {
            return respuesta.pregunta && respuesta.pregunta.tipo === 'archivo';
        },
        urlArchivo(respuesta) {
            return '/admin/campanas/' + this.rowData.campaign_id
                + '/suscripcion-respuesta/' + respuesta.id + '/archivo';
        },
    },
};
</script>
