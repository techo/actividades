<template>
	<div>
		<div class="row">
			<div class="col-md-4 col-sm-6 col-xs-12">
				<div class="info-box">
					<span class="info-box-icon bg-aqua">
						<i class="fa fa-users"></i>
					</span>
					<div class="info-box-content text-center">
						<span class="info-box-number"><h3>{{ inscripciones }}</h3></span>
						<span class="info-box-text">Inscripciones</span>
					</div>
				</div>
			</div>

			<div class="col-md-4 col-sm-6 col-xs-12">
				<div class="info-box">
					<span class="info-box-icon bg-green">
						<i class="fa fa-check-circle"></i>
					</span>
					<div class="info-box-content text-center">
						<span class="info-box-number"><h3>{{ presentes }}</h3></span>
						<span class="info-box-text">Presentes</span>
					</div>
				</div>
			</div>

			<div class="col-md-4 col-sm-6 col-xs-12">
				<div class="info-box">
					<span class="info-box-icon bg-red">
						<i class="fa fa-times-circle"></i>
					</span>
					<div class="info-box-content text-center">
						<span class="info-box-number"><h3>{{ ausentes }}</h3></span>
						<span class="info-box-text">Ausentes</span>
					</div>
				</div>
			</div>
		</div>
		<div class="box" >
			<div class="box-header" >
				<h2 class="box-title">
					<strong>Inscripciones del usuario</strong>
				</h2>
				<span class="pull-right">
				<a :href="urlDescarga" class="btn btn-primary">
					<i class="fa fa-download" ></i>
					Descargar
				</a>
			</span>
			</div>
			<div class="box-body" >

				<div class="table-responsive" style="min-height: 450px">
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

					<br/>

					<vuetable-pagination 
						ref="pagination"
						@vuetable-pagination:change-page="onChangePage"
						:css="css.pagination"
					></vuetable-pagination>
				</div>

			</div>
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
				inscripciones: 0,
				presentes: 0,
				ausentes: 0,
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
			this.urlDescarga = "/admin/usuarios/" + this.persona + "/exportar-inscripciones";
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
		},
		mounted () {
			axios.get("/admin/ajax/usuarios/" + this.persona + "/inscripciones-stats")
				.then(data => {
					this.inscripciones = data.data.inscripciones;
					this.presentes = data.data.presentes;
					this.ausentes = data.data.ausentes;
				})
				.catch(error => console.log(error));
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