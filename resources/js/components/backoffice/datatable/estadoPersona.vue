<template>
    <span>
        <span class="badge badge-pill m-2 text-center " :class="color">
        {{ estado }}
        </span>

        <span v-if="esDeLasUltimas24Horas" class="badge badge-pill bg-techo-violet m-2 text-center">
        {{ $t('backend.new_inscription') }}
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
            if(this.rowData.estado_voluntario){
                this.estado = this.rowData.estado_voluntario;
                this.color = "label-default";
                if(this.estado == "Suspendido")
                    this.color = "label-warning";
                else if (this.estado == "Desvinculado")
                    this.color = "label-danger";


            } else {
                this.color = "label-default";
            }
        },
        computed: {
            esDeLasUltimas24Horas() {
                return new Date() - new Date(this.rowData.fechaInscripcion) <= 24 * 60 * 60 * 1000;

            }
        }
    }
</script>

<style scoped>

</style>