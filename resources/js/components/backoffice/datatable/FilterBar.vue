<template>
    <span class="filter-bar">
        <form class="form-inline">
            <div class="form-group">
                <label>{{ $t('backend.filter_by') }}</label>
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
                <button class="btn btn-default" @click.prevent="exportar">
                    <i class="glyphicon glyphicon-save-file"></i> {{ $t('backend.export_to_excel') }}
                </button>

            </div>
        </form>
    </span>
</template>

<script>
    export default {
        name: "FilterBar",
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
