<template>
	<div>
		<alert :mostrar="loading"></alert>
		<vuetable
		class="vuetable"
		ref="vuetable"
		v-bind:api-url="apiUrl"
		:fields="fields"
		pagination-path=""
		:css="css.table"
		:sort-order="sortOrder"
		:multi-sort="true"
		:append-params="moreParams"
		:perPage="25"
		no-data-template="No hay ítems para mostrar"
		@vuetable:cell-clicked="onCellClicked"
		@vuetable:pagination-data="onPaginationData"
		@vuetable:loading="loading = true"
		@vuetable:loaded="loading = false"
		></vuetable>
		<div class="vuetable-pagination">
			<vuetable-pagination-info ref="paginationInfo"
			info-class="pagination-info"
			infoTemplate="Ítem {from} a {to} de {total}"
			noDataTemplate="No hay ítems para mostrar"
			></vuetable-pagination-info>
			<vuetable-pagination ref="pagination"
			:css="css.pagination"
			@vuetable-pagination:change-page="onChangePage"
			></vuetable-pagination>
		</div>
	</div>
</template>

<script>
	import Alert from './Alert';
	import Vuetable from 'vuetable-2/src/components/Vuetable'
	import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
	import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'

	export default {
		components: {
			Alert,
			Vuetable,
			VuetablePagination,
			VuetablePaginationInfo,
		},
		props: ['apiUrl', 'fields', 'sortOrder', 'detailUrl', 'moreParams'],
		data () {
			return {
				loading: false,
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
				//moreParams: {}
			}
		},
		methods: {
			onPaginationData (paginationData) {
				this.$refs.pagination.setPaginationData(paginationData);
				this.$refs.paginationInfo.setPaginationData(paginationData);
			},
			onChangePage (page) {
				this.$refs.vuetable.changePage(page)
			},
			onCellClicked (data, field, event) {
				if (this.detailUrl !== undefined) {
					window.open(this.detailUrl + data.id);
				}
				this.$refs.vuetable.toggleDetailRow(data.id)
			}
		},
		events: {
			'filter-set' (filterText) {
				this.moreParams = {
					filter: filterText
				};
				Vue.nextTick( () => this.$refs.vuetable.refresh() )
			},
			'filter-reset' () {
				this.moreParams = {};
				Vue.nextTick( () => this.$refs.vuetable.refresh() )
			}
		}
	}
</script>
<style>
.pagination {
	margin: 0;
	float: right;
}
.pagination a.page {
	border: 1px solid lightgray;
	border-radius: 3px;
	padding: 5px 10px;
	margin-right: 2px;
	cursor: pointer;
}
.pagination a.page.active {
	color: white;
	background-color: #337ab7;
	border: 1px solid lightgray;
	border-radius: 3px;
	padding: 5px 10px;
	margin-right: 2px;
}
.pagination a.btn-nav {
	border: 1px solid lightgray;
	border-radius: 3px;
	padding: 5px 7px;
	margin-right: 2px;
	cursor: pointer;
}
.pagination a.btn-nav.disabled {
	color: lightgray;
	border: 1px solid lightgray;
	border-radius: 3px;
	padding: 5px 7px;
	margin-right: 2px;
	cursor: not-allowed;
}
.pagination-info {
	float: left;
}

.vuetable tr {
	cursor: pointer;
}

.vuetable tr td:hover{
	text-decoration: underline;
}
</style>
