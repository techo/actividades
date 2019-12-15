<template>
    <div>
        <puntos-modal :id="id" ></puntos-modal>
        <div class="box">
            <div class="box-header with-border">
            </div>
            <div class="box-body">
                <span class="pull-right">
                    <button class="btn btn-primary" @click.prevent="desplegarModal()">Crear <i class="fa fa-plus"></i></button>
                </span>
                <br>
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
    import puntosModal from './puntos-modal'

    export default {
        name: "puntos",
        props: ['id', 'fields', 'sortOrder'],
        components: {'vuetable-puntos': Vuetable, 'puntos-modal': puntosModal },
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
            desplegarModal() {
                Event.$emit('puntos:crear');
            }
        }
    }
</script>

<style scoped>

</style>