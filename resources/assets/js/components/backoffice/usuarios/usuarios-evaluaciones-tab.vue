<template>
	<div class="box" >
		<div class="box-header" >
			<h2 class="box-title">
				<strong>Promedios de evaluaciones recibidas</strong>
			</h2>
			<span class="pull-right">
				<a :href="urlDescarga" class="btn btn-primary">
					<i class="fa fa-download" ></i>
					Descargar detalle
				</a>
			</span>
			<div class="row">
				<div class="col-md-12">
					<p>
						<b>Puntaje técnico</b>: conocimientos sobre la tarea a desarrollar.
					</p>
					<p>
						<b>Puntaje social</b>: habilidades para comuncicarse y empatizar con otros.
					</p>
				</div>
			</div>
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
				urlDescarga: "",
				fields: [
					{ title: 'Actividad', name: 'nombreActividad', sortField: 'nombreActividad', },
					{ title: 'Tipo',  name: 'nombre', sortField: 'nombre', },
					{
						title: 'Fecha',
						name: 'fechaInicio',
						callback: function (value) {
						    return window.moment(value).format('DD/MM/YYYY hh:mm');
						},
						sortField: 'fechaInicio',
					},
					{ title: 'Promedio técnico', name: 'puntajeTecnico', sortField: 'puntajeTecnico', },
					{ title: 'Promedio social', name: 'puntajeSocial', sortField: 'puntajeSocial',  },
					{ title: 'Comentario', name: 'comentario', sortField: 'comentario', },
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
			this.url = "/admin/ajax/usuarios/" + this.persona + "/evaluaciones";
			this.urlDescarga = "/admin/usuarios/" + this.persona + "/exportar-evaluaciones";
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