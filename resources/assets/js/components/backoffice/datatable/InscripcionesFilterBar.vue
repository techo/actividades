<template>
    <div class="filter-bar">
        <div class="row">
            <div class="col-md-7">
                <form class="form-inline">
                    <div class="form-group">
                        <label>Filtrar por:</label>
                        <input
                                type="text"
                                v-model="filterText"
                                class="form-control input"
                                @keyup.enter="doFilter"
                                :placeholder="dataPlaceholderText"
                                style="width: 20em"
                        >
                        <button class="btn btn-primary" @click.prevent="doFilter">Buscar</button>
                        <button class="btn" @click.prevent="resetFilter">Borrar</button>
                    </div>
                </form>
            </div>
            <div class="col-md-3 pull-right">
                <button class="btn" @click.prevent="mostrarModalImportar">
                    <i class="glyphicon glyphicon-upload"></i> Importar desde Excel
                </button>
                <inscripciones-importar-modal></inscripciones-importar-modal>
            </div>
            <div class="col-md-2 pull-right">
                <button class="btn" @click.prevent="exportar">
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
    export default {
        props: ['placeholderText'],
        data() {
            return {
                filterText: '',
                dataPlaceholderText: this.placeholderText
            }
        },
        methods: {
            doFilter() {
                this.$events.fire('filter-set', this.filterText)
            },
            resetFilter() {
                this.filterText = '';
                this.$events.fire('filter-reset');
            },
            exportar() {
                let url = window.location.href;
                url = url.slice(-1) !== "/" ? url + "/" : url;
                let filter = this.$parent.moreParams.filter !== undefined ? 'filter=' + this.$parent.moreParams.filter + '&' : '';
                location.href = url + 'inscripciones/exportar?' + filter + 'condiciones=' + JSON.stringify(this.$parent.moreParams.condiciones)
            },
            mostrarModalImportar() {
                Event.$emit('inscripciones:importar-button-clicked');
            }
        }
    }
</script>
<style>
    .filter-bar {
        padding: 10px;
    }

</style>
