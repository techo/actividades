<template>
	<div class="box" >
		<div class="box-header" >
			<h2 class="box-title">
				<strong>{{ $t('backend.studies') }}</strong>
			</h2>
		</div>
		<div class="box-body" >

			<div class="table-responsive" style="min-height: 450px" >
				<vuetable
					ref="vuetable"
					:api-url="url"
					:fields="fields"
					pagination-path=""
					data-path="data"
					:http-fetch="myFetch"
					@vuetable:pagination-data="onPaginationData"
					:css="css.table"
				></vuetable>

				<vuetable-pagination 
					ref="pagination"
					@vuetable-pagination:change-page="onChangePage"
					:css="css.pagination"
				></vuetable-pagination>
			</div>

			<br/>
			<br/>

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
					{ title: 'Título', name: 'titulo', sortField: 'titulo', },
					{ title: 'Institución',  name: 'institucion_educativa', sortField: 'institucion_educativa', },
					{ title: 'Disciplina', name: 'disciplina_academica', sortField: 'disciplina_academica', },
					{ title: 'Descripción', name: 'descripcion_educacion', sortField: 'descripcion_educacion',  },
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

		},
		created () {
			this.url = "/admin/ajax/usuarios/" + this.persona + "/estudios";
		},
		mounted () {
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