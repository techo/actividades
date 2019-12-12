<template>
    <div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Básica</h3>
            </div>
            <div class="box-body">
                <vuetable-puntos 
                    :api-url="url" 
                    :fields="fields_" 
                    pagination-path="" 
                    data-path=""

                    class="vuetable"
                    :css="css.table"
                    >
                </vuetable-puntos>
            </div>
        </div>
    </div>
</template>

<script>

    import Vuetable from 'vuetable-2'

    export default {
        name: "puntos",
        props: ['id', 'fields', 'sortOrder'],
        components: {'vuetable-puntos': Vuetable},
        data() {
            return {
                url: '/admin/ajax/actividades/' + this.id + '/puntos',
                sortOrder_: [],
                fields_: [],
                css: {
                    table: {
                        tableClass: 'table table-hover table-condensed',
                        ascendingIcon: 'glyphicon glyphicon-chevron-up',
                        descendingIcon: 'glyphicon glyphicon-chevron-down'
                    },
                    pagination: {
                        wrapperClass: 'pagination',
                        activeClass: 'active',
                        disabledClass: 'disabled',
                        pageClass: 'page',
                        linkClass: 'link',
                        icons: {
                            first: '',
                            prev: '',
                            next: '',
                            last: '',
                        },
                    },
                    icons: {
                        first: 'glyphicon glyphicon-step-backward',
                        prev: 'glyphicon glyphicon-chevron-left',
                        next: 'glyphicon glyphicon-chevron-right',
                        last: 'glyphicon glyphicon-step-forward',
                    },
                },
            }
        },
        created() {
            this.fields_ = JSON.parse(this.fields);
            this.sortOrder_ = JSON.parse(this.sortOrder);
        },
        mounted() {
            Event.$on('guardar', this.guardar);
        },
        computed: {},
        filters: {},
        watch: {},
        methods: {
            guardar(){
                axios.post('/admin/ajax/actividades/' + this.id, this.actividad)
                    .then((datos) => { this.actividad = datos.data; })
                    .catch((error) => { debugger; });
            },
        }
    }
</script>

<style scoped>

</style>