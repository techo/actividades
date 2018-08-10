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
                <button class="btn btn-default" @click.prevent="resetFilter">Borrar</button>
            </div>
            <grupos-toolbar
                    class="pull-right"
                    asistencia="false"
                    estado="false"
                    evento="grupos-checkbox-toggled"
            >
            </grupos-toolbar>
        </form>
    </div>
</template>

<script>
    import GruposToolbar from '../grupos/GruposToolbar'
    export default {
        props: ['placeholderText'],
        components: {'grupos-toolbar': GruposToolbar},
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
            }
        }
    }
</script>
<style>
    .filter-bar {
        padding: 10px;
    }

</style>
