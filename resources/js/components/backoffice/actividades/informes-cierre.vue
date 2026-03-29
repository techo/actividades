<template>
    <div>
        <informes-modal :actividad="actividad" @refrescar="refrescar"></informes-modal>
        <div class="box">
                <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <span class="pull-right">
                            <button class="btn btn-primary" @click.prevent="desplegarModal()">{{ $t('backend.create') }} <i class="fa fa-plus"></i></button>
                        </span>
                    </div>
                </div>
                <br>
                <div class="table-responsive">
                    <vuetable-informes 
                        :api-url="url" 
                        :fields="fields_" 
                        pagination-path="" 
                        data-path=""
                        v-on:vuetable:row-clicked="editar"

                        class=""
                        :css="css.table"
                        ref="tabla"
                        >
                    </vuetable-informes>
                </div>
            </div>

        </div>
    </div>
</template>

<script>

    import Vuetable from 'vuetable-2'
    import informesModal from './informes-modal'

    export default {
        name: "informes",
        props: ['actividad', 'fields', 'sortOrder'],
        components: {'vuetable-informes': Vuetable, 'informes-modal': informesModal },
        data() {
            return {
                url: '/admin/ajax/actividades/' + this.actividad.idActividad + '/informe_cierre',
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
            this.sortOrder_ = JSON.parse(this.sortOrder);
            let fields = JSON.parse(this.fields);

            fields = fields.map(field => {
                if (field.title) {
                    field.title = this.translateField(field.title);
                }
                return field;
            });

            this.fields_ = fields;
        },
        mounted() {
            Event.$on('informes:refrescar', this.refrescar);
        },
        computed: {},
        filters: {},
        watch: {},
        methods: {
            desplegarModal() {
                Event.$emit('informes:crear');
            },
            refrescar() {
                this.$refs.tabla.resetData();
                this.$nextTick( () => this.$refs.tabla.refresh());
            },
            editar(p) {
                Event.$emit('informes:editar', p);
            },
            estado(v) {
                return (v=='1')?'Activo':'Inactivo';
            },
            translateField(title) {
                if (!title) return title;

                // si es una key de traducción
                if (title.includes('.')) {
                    return this.$t(title);
                }

                return title;
            },
        }
    }
</script>

<style scoped>

</style>