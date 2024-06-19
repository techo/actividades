<template>
    <div class="filter-bar">
        <div class="row">
            <div class="col-md-6">

                <form class="form-inline">
                    <div class="form-group">
                        <label>Filtrar por:</label>
                        <input
                                type="text"
                                v-model="filterText"
                                class="form-control input"
                                @keyup.enter="doFilter"
                                :placeholder="dataPlaceholderText"
                                style="width: 18em"
                        >
                        <button class="btn btn-primary" @click.prevent="doFilter">{{ $t('backend.search') }}</button>
                        <button class="btn btn-default" @click.prevent="resetFilter">{{ $t('backend.delete') }}</button>
                    </div>
                </form>
            </div>

            <inscripciones-importar-modal ></inscripciones-importar-modal>
            <inscripciones-inscribir-modal :idActividad="idActividad" ></inscripciones-inscribir-modal>
            <div class="col-md-6" style="text-align: right">
                <button class="btn btn-primary" @click.prevent="mostrarModalInscribir">
                    <i class="fa fa-plus"></i> {{ $t('backend.register') }}
                </button>
                <button class="btn btn-default" @click.prevent="mostrarModalImportar">
                    <i class="glyphicon glyphicon-upload"></i> {{ $t('backend.import_from_excel') }}
                </button>
                <button class="btn btn-default" @click.prevent="exportar">
                    <i class="glyphicon glyphicon-save-file"></i> {{ $t('backend.export_to_excel') }}
                </button>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                <inscripciones-toolbar></inscripciones-toolbar>
            </div>
        </div>
    </div>
</template>

<script>
    import InscripcionesInscribirModal from '../actividades/inscripciones-inscribir-modal.vue';

    export default {
        props: ['placeholderText', 'idActividad'],
        components: { 'inscripciones-inscribir-modal': InscripcionesInscribirModal },
        data() {
            return {
                filterText: '',
                dataPlaceholderText: this.placeholderText
            }
        },
        methods: {
            doFilter() {
                this.$events.fire('filter-set-inscripciones', this.filterText)
            },
            resetFilter() {
                this.filterText = '';
                this.$events.fire('filter-reset-inscripciones');
            },
            exportar() {
                let filter = this.$parent.moreParams.filter !== undefined ? 'filter=' + this.$parent.moreParams.filter + '&' : '';
                location.href = 'inscripciones/exportar?' + filter + 'condiciones=' + JSON.stringify(this.$parent.moreParams.condiciones)
            },
            mostrarModalImportar() {
                Event.$emit('inscripciones:importar-button-clicked');
            },
            mostrarModalInscribir() {
                Event.$emit('inscripciones:inscribir-button-clicked');
            }
        }
    }
</script>
<style>
    .filter-bar {
        padding: 10px;
    }

</style>
