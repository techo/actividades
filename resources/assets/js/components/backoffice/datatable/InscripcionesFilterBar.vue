<template>
    <div class="filter-bar">
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
                <button class="btn" @click.prevent="exportar">
                    <i class="glyphicon glyphicon-save-file"></i> Exportar a Excel
                </button>
            </div>
            <inscripciones-toolbar class="pull-right"></inscripciones-toolbar>
        </form>
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
                location.href = location.href + '/exportar?filter=' + this.filterText
            }
        }
    }
</script>
<style>
    .filter-bar {
        padding: 10px;
    }

</style>
