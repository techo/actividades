<template>
    <select name="estadoInscripcion" id="estadoInscripcion" v-model="estado">
        <option value="Confirmado">Confirmado</option>
        <option value="Sin Confirmar">Sin Confirmar</option>
        <option value="Sin Interés">Sin Interés</option>
        <option value="Sin Contactar">Sin Contactar</option>
    </select>
</template>

<script>
    export default {
        name: "estadoInscripcion",
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
                estado: this.rowData.estado,
                idInscripcion: this.rowData.id
            }
        },
        watch: {
            estado() {
                Event.$emit('estado_' + this.idInscripcion, this.estado);
            },
            'rowData.estado': function () {
                this.estado = (this.rowData.estado === null) ? 0 : this.rowData.estado;
            }
        }
    }
</script>

<style scoped>

</style>