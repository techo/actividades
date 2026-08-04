<template>
    <span>
        <!-- El email ya corresponde a un usuario (Persona) existente. -->
        <span v-if="rowData.persona_id">
            <span class="label label-info" style="font-size:0.85em;">
                <i class="fa fa-user"></i> {{ $t('backend.user_exists') }}
            </span>
            <a :href="'/admin/usuarios/' + rowData.persona_id" target="_blank"
               class="btn btn-xs btn-default" @click.stop>
                <i class="fa fa-external-link"></i> {{ $t('backend.view_person') }}
                <span v-if="rowData.persona_nombre"> — {{ rowData.persona_nombre }}</span>
            </a>
        </span>
        <!-- Ya convertido. -->
        <span v-else-if="rowData.convertido" class="text-success">
            <i class="fa fa-check"></i> {{ $t('backend.yes') }}
        </span>
        <!-- Sin usuario todavía: botón para convertir. -->
        <button v-else class="btn btn-xs btn-warning" @click.stop="convertir">
            <i class="fa fa-user-plus"></i> {{ $t('backend.convert_to_user') }}
        </button>
    </span>
</template>

<script>
    /**
     * Celda del listado de suscriptos que preserva el comportamiento del listado
     * bespoke: detecta usuario existente, muestra "convertido" o el botón para
     * convertir. El alta la maneja el datatable (emite `suscriptos:convertir`).
     */
    export default {
        name: 'celda-convertido',
        props: ['rowData', 'rowField', 'rowIndex', 'fieldDef'],
        methods: {
            convertir() {
                Event.$emit('suscriptos:convertir', this.rowData);
            },
        },
    };
</script>
