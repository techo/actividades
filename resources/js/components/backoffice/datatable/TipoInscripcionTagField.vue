<template>
    <span>
        <span v-for="(label, i) in labels" :key="i" class="label label-primary">
            {{ label }}
        </span>
    </span>
</template>

<script>
    export default {
        name: "tag_field",
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                type: Number
            }
        },
        data() {
            return {
                items: [],
            }
        },
        computed: {
            // Etiqueta legible de cada tipo aplicado, filtrando vacíos.
            labels() {
                return this.items
                    .map(item => this.labelDe(item))
                    .filter(label => label !== '' && label !== null && label !== undefined);
            }
        },
        methods: {
            // El item puede venir como objeto { id, text } (formato legacy) o como
            // un id/slug pelado (formato actual, ej. "voluntariado"). En ambos casos
            // resolvemos el texto legible.
            labelDe(item) {
                if (item && typeof item === 'object') {
                    if (item.text) return item.text;
                    return this.traducir(item.id);
                }
                return this.traducir(item);
            },
            traducir(id) {
                if (id === null || id === undefined || id === '') return '';
                var clave = 'backend.tipo_voluntariado_options.' + id;
                var texto = this.$t(clave);
                // Si no hay traducción, vue-i18n devuelve la propia clave: en ese
                // caso mostramos el valor crudo para no dejar un label vacío.
                return (!texto || texto === clave) ? String(id) : texto;
            }
        },
        mounted() {
            var valor = this.rowData.inscripciones_aplicadas;
            if (!valor) return;

            if (Array.isArray(valor)) {
                this.items = valor;
                return;
            }

            try {
                var cadenaJSONSinComillas = valor.replace(/^"|"$/g, '');
                var cadenaJSONSinEscape = cadenaJSONSinComillas.replace(/\\\"/g, '"');
                this.items = JSON.parse(cadenaJSONSinEscape);
            } catch (e) {
                this.items = [];
            }
        },
    }
</script>

<style scoped>

</style>
