<template>
    <span>
        <span  class="label " :class="color">
            {{ estado }}
        </span>
    </span>
</template>

<script>
    export default {
        name: "estadoPersona",
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
                estado: "",
                color: "",
            }
        },

        mounted() {
            if(this.rowData.estadoPersona){
                this.estado = this.rowData.estadoPersona;
                if(this.estado == "Suspendido")
                    this.color = "label-warning";
                else if (this.estado == "Desvinculado")
                    this.color = "label-danger";
            } else {
                this.estado = "Habilitado";
                this.color = "label-secondary";
            }
        },
    }
</script>

<style scoped>

</style>