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

            const value = this.rowData[field];
            return this.$t(`backend.${field}_options.${value}`) || value;
        }
    }
}
</script>
