<template>
    <span>
        <span>{{ translatedValue }}</span>
    </span>
</template>

<script>
export default {
    name: "translate_backend",
    props: {
        rowData: {
            type: Object,
            required: true
        },
        rowIndex: {
            type: Number
        }
    },
    computed: {
        translatedValue() {
            const componentName = this.$options._componentTag || this.$options.name || '';
            const parts = componentName.split('_');
            let field = parts[1] || 'programa'; // valor por defecto

            field = field.replace(/([a-z0-9])([A-Z])/g, '$1_$2').toLowerCase();

            let value = '';

            if ( parts[1] === 'rolesActividad') {
                value = this.rowData['nombreRol'];
            } else {
                value = this.rowData[field];
            }
            if (value === null || value === undefined || value === '') {
                return '';
            }
            // vue-i18n devuelve la propia clave cuando no hay traducción, así que
            // chequeamos existencia con $te y, si no está, mostramos el valor crudo
            // (hay roles legacy/fuera de catálogo que no tienen clave).
            const key = `backend.${field}_options.${value}`;
            return this.$te(key) ? this.$t(key) : value;
        }
    }
}
</script>
