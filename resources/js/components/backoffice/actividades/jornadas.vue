<template>
    <div>
        <jornadas-modal :actividad="actividad_" ></jornadas-modal>
        <div class="box">
            <div class="box-header">
                <p class="help-block">
                    {{ $t('backend.meeting_points_description') }}
                </p>
                <p class="help-block">
                    {{ $t('backend.activity_to_show_on_site') }} <b>{{ $t('backend.must_have_active_meeting_points') }}</b>
                </p>
            </div>

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

                    <generic-datatable  
                        :api-url="apiUrl"
                        :fields="fields"
                        :sort-order = "sortOrder"
                        :placeholder-text="$t('backend.search_by_name_or_role')"
                        ref="jornadasTabla"
                        @onClickRow="editJornada"
                    ></generic-datatable>
                </div>
            </div>

        </div>
    </div>
</template>

<script>

    import jornadasModal from './jornadas-modal'

    export default {
        name: "jornadas",
        props: ['apiUrl','actividad', 'fields', 'sortOrder'],
        components: { 'jornadas-modal': jornadasModal },
        data() {
            return {
                url: '/admin/ajax/actividades/' + this.id + '/jornadas',
                sortOrder_: [],
                fields_: [],
                actividad_: [],
            }
        },
        created() {
            this.actividad_ = JSON.parse(this.actividad);
            this.fields_ = JSON.parse(this.fields);
            this.sortOrder_ = JSON.parse(this.sortOrder);
        },
        mounted() {
            Event.$on('jornadas:refrescar', this.refrescar);
        },
        computed: {},
        filters: {},
        watch: {},
        methods: {
            guardar(){
                axios.post('/admin/ajax/actividades/' + this.idActividad, this.actividad)
                    .then((datos) => { this.actividad = datos.data; })
                    .catch((error) => { debugger; });
            },
            desplegarModal() {
                Event.$emit('jornadas:crear');
            },
            editJornada(p) {
                console.log("holas")
                Event.$emit('jornadas:editar', p);

            },
            refrescar() {
                this.$refs.jornadasTabla.resetData();
                this.$nextTick( () => this.$refs.jornadasTabla.refresh());
            },
            editar(p) {
                Event.$emit('jornadas:editar', p);
            },
            estado(v) {
                return (v=='1')?'Activo':'Inactivo';
            }
        }
    }
</script>

<style scoped>

</style>