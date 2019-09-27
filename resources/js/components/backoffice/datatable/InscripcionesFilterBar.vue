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
                        <button class="btn btn-primary" @click.prevent="doFilter">Buscar</button>
                        <button class="btn btn-default" @click.prevent="resetFilter">Borrar</button>
                    </div>
                </form>
            </div>

            <inscripciones-importar-modal ></inscripciones-importar-modal>
            <inscripciones-inscribir-modal :idActividad="idActividad" ></inscripciones-inscribir-modal>
            <div class="col-md-6" style="text-align: right">
                <button class="btn btn-primary" @click.prevent="mostrarModalInscribir">
                    <i class="fa fa-plus"></i> inscribir
                </button>
                <button class="btn btn-default" @click.prevent="mostrarModalImportar">
                    <i class="glyphicon glyphicon-upload"></i> Importar desde Excel
                </button>
                <button class="btn btn-default" @click.prevent="exportar">
                    <i class="glyphicon glyphicon-save-file"></i> Exportar a Excel
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
                let url = window.location.href;
                url = url.slice(-1) !== "/" ? url + "/" : url;
                let filter = this.$parent.moreParams.filter !== undefined ? 'filter=' + this.$parent.moreParams.filter + '&' : '';
                location.href = url + 'inscripciones/exportar?' + filter + 'condiciones=' + JSON.stringify(this.$parent.moreParams.condiciones)
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
