<template>
    <span class="filter-bar">
        <form class="form-inline">
            <div class="form-group">
                <label>{{ $t('backend.filter_by') }}:</label>
                <input
                        type="text"
                        v-model="filterText"
                        class="form-control input"
                        @keyup.enter="doFilter"
                        :placeholder="dataPlaceholderText"
                        style="width: 20em"
                >
                <button class="btn btn-primary" @click.prevent="doFilter">{{ $t('backend.filter') }}</button>
                <button class="btn btn-default" @click.prevent="resetFilter">{{ $t('backend.delete') }}</button>
            </div>
        </form>
    </span>
</template>

<script>
    export default {
        name: "UsuariosFilterBar",
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
        }
    }
</script>
<style>
    .filter-bar {
        padding: 10px;
    }

</style>
