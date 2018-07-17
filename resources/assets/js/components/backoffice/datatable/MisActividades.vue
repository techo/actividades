<template>
    <span>
        <button class="btn btn-default btn-sm" @click="verDetalle">
            <i class="glyphicon glyphicon-zoom-in"></i>
        </button>
        <button class="btn btn-default btn-sm" @click="verInscriptos">
            Ver Inscripciones
        </button>
    </span>
</template>

<script>
    export default {
        name: "MisActividades",
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                //type: Number
            }
        },
        data() {
            return {
                idActividad: this.rowData.id
            }
        },
        watch: {
            rowData: function () {
                this.idActividad = this.rowData.id;
            }
        },
        methods: {
            verDetalle: function () {
                let url = '/admin/actividades/' + this.idActividad;
                location.href = url;
            },

            verInscriptos: function () {
                let url = '/admin/actividades/' + this.idActividad + '/inscripciones';
                location.href = url;
            }
        }
    }
</script>

<style scoped>

</style>