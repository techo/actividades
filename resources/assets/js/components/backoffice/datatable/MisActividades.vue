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
                type: Number
            }
        },
        data() {
            return {
                idActividad: this.rowData.id
            }
        },
        methods: {
            verDetalle: function () {
                let lastSlash = location.href.lastIndexOf('/');
                let url = location.href.substr(0, lastSlash) + '/' + this.idActividad;

                location.href = url;
            },

            verInscriptos: function () {
                let lastSlash = location.href.lastIndexOf('/');
                let url = location.href.substr(0, lastSlash) + '/' + this.idActividad + '/inscripciones';

                location.href = url;
            }
        }
    }
</script>

<style scoped>

</style>