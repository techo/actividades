<template>
    <div class="filter-bar">
        <form class="form-inline">

            <div class="form-group">
                <label class="label-filtrar">{{ $t('frontend.filter_by') }}:</label>
                <input
                        type="text"
                        v-model="filterText"
                        class="form-control input"
                        @keyup.enter="doFilter"
                        :placeholder="dataPlaceholderText"
                        style="width: 35em"
                >
                <button class="btn btn-primary" @click.prevent="doFilter">{{ $t('frontend.filter') }}</button>
                <button class="btn btn-default" @click.prevent="resetFilter">{{ $t('frontend.delete') }}</button>
            </div>
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
        }
    }
</script>
<style>
    .filter-bar {
        padding: 10px;
    }

    button.btn {
        margin-left: 5px;
    }

    .label-filtrar {
        margin-right: 5px;
        font-size: larger;
    }
</style>
