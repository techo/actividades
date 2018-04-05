<template>
    <div>
        <table class="table table-hover table-condensed" id="tabla">
            <thead>
            <tr>
                <th v-for="titulo in dataEncabezados">{{ titulo }}</th>
            </tr>
            </thead>
        </table>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "datatable",
        props: ['encabezados', 'url'],
        data() {
            return {
                dataEncabezados: [],
                dataUrl: this.url
            }
        },
        created() {
            this.dataEncabezados = JSON.parse(this.encabezados);
        },
        mounted() {
            $(document).ready(function() {
            self = this;
            const oTable = $('#tabla').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": self.dataUrl,
                "columns": [
                    {data: 'idActividad', name: 'idActividad'},
                    {data: 'nombreActividad', name: 'nombreActividad'},
                    {data: 'fechaInicio', name: 'fechaInicio'},
                    {data: 'fechaFin', name: 'fechaFin'},
                    {data: 'idUnidadOrganizacional', name: 'idUnidadOrganizacional'},
                    {data: 'state', name: 'state'}
                ]
            });
        });

        }
    }
</script>


<style scoped>

</style>