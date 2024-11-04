<template>
    <span>
        <img v-if="rowData.photo" class="imagen-perfil-mini" :src="'/'+rowData.photo" alt="Foto">
        <img v-else src="/bower_components/admin-lte/dist/img/user_avatar.png" class="imagen-perfil-mini" alt="User Image">
    </span>
</template>

<script>
    export default {
        name: "photo",
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
            // if(this.rowData.photo){
            //     var cadenaJSONSinComillas = this.rowData.roles_aplicados.replace(/^"|"$/g, '');
            //     var cadenaJSONSinEscape = cadenaJSONSinComillas.replace(/\\\"/g, '"');
            //     this.items = JSON.parse(cadenaJSONSinEscape);
            // }
        },
    }
</script>

<style scoped>

</style>