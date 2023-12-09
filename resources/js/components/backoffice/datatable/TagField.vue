<template>
    <span>
        <span v-for="item in items" class="label label-primary">
            {{ item.text }}
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

        mounted() {
            if(this.rowData.roles_aplicados){
                var cadenaJSONSinComillas = this.rowData.roles_aplicados.replace(/^"|"$/g, '');
                var cadenaJSONSinEscape = cadenaJSONSinComillas.replace(/\\\"/g, '"');
                this.items = JSON.parse(cadenaJSONSinEscape);
            }
        },
    }
</script>

<style scoped>

</style>