<template>
	<div class="box" >
		<div class="box-body" >

			<vuetable
				ref="vuetable"
				:api-url="url"
				:fields="fields"
				pagination-path=""
				data-path="data"
				:http-fetch="myFetch"
				@vuetable:pagination-data="onPaginationData"
				style="min-height: 450px"
				:css="css.table"
			></vuetable>

			<br/>

			<vuetable-pagination 
				ref="pagination"
				@vuetable-pagination:change-page="onChangePage"
				:css="css.pagination"
			></vuetable-pagination>

		</div>
	</div>
</template>
<script>
	import axios from 'axios';

	import Vuetable from 'vuetable-2/src/components/Vuetable'
	import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'

	export default {
		components: { Vuetable, VuetablePagination },
		props: [ 'persona' ],
		data: function () {
			return {
				url: "",
				fields: [
					{ title: 'Actividad', name: 'nombreActividad', sortField: 'nombreActividad', },
					{ title: 'Tipo', name: 'nombre', sortField: 'nombre', },
					{
						title: 'Fecha',
						name: 'fechaInscripcion',
						callback: function (value) {
						    return window.moment(value).format('DD/MM/YYYY hh:mm');
						},
						sortField: 'fechaInscripcion',
					},
					{ title: 'Rol', name: 'rol', sortField: 'rol', },
					{ title: 'Estado', name: 'estado', sortField: 'estado', },
					{
						title: 'Presente',
						name: 'presente',
						callback: function (value) {
							return value === 0 ? 'Ausente' : 'Presente';
						},
						sortField: 'presente',
					}
				],
				css: {
					table: {
				        tableClass: 'table table-hover table-condensed',
				        ascendingIcon: 'glyphicon glyphicon-chevron-up',
				        descendingIcon: 'glyphicon glyphicon-chevron-down',
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
					icons: {
						first: 'glyphicon glyphicon-step-backward',
						prev: 'glyphicon glyphicon-chevron-left',
						next: 'glyphicon glyphicon-chevron-right',
						last: 'glyphicon glyphicon-step-forward',
			        }
				},
				}
			}
		},
		created () {
			this.url = "/admin/ajax/usuarios/" + this.persona + "/inscripciones";
		},
		computed: {
		},
		watch: {
		},
		methods: {
			myFetch (apiUrl, httpOptions) {
				return axios.get(apiUrl, httpOptions);
			},
			onPaginationData (paginationData) {
				this.$refs.pagination.setPaginationData(paginationData)
			},
			onChangePage (page) {
				this.$refs.vuetable.changePage(page)
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