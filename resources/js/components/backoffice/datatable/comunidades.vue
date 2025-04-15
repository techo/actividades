<template>
    <span>
        <span v-for="(item, index) in comunidadesArray" :key="index" class="label label-primary">
            {{ item }}
        </span>
    </span>
</template>

<script>
export default {
    name: "tag_comunidades",
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
        comunidadesArray() {
            if (!this.rowData.comunidades) return [];
            return this.rowData.comunidades.split(';').map(c => c.trim());
        }
    }
}
</script>
