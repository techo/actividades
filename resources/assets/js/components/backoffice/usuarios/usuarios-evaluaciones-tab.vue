<template>
	<div class="box" >
		<div class="box-body" >

			<div class="row">
				<div class="col-md-12" style="text-align: right">
					<a :href="urlDescarga" class="btn btn-default">
						<i class="fa fa-download" ></i>
						Descargar detalle
					</a>
				</div>
			</div>

			<div style="justify-content: center;  display: flex;">
				<bar-chart :chart-data="chart.data" :options="options" :width="500" :height="250"></bar-chart>
			</div>

			<br>

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

			<vuetable-pagination 
				ref="pagination"
				@vuetable-pagination:change-page="onChangePage"
				:css="css.pagination"
			></vuetable-pagination>

			<br/>
			<br/>

		</div>
	</div>
</template>
<script>
	import axios from 'axios';

	import Vuetable from 'vuetable-2/src/components/Vuetable'
	import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
	import BarChart from '../../plugins/BarChart'

	export default {
		components: { Vuetable, VuetablePagination, BarChart },
		props: [ 'persona' ],
		data: function () {
			return {
				url: "",
				urlDescarga: "",
				chart: {
					data: null,
				},
				options: {
                    scales: {
                        yAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: "Cantidad evaluaciones"
                            },
                            ticks: {
                                beginAtZero: true,
                                userCallback: function(label, index, labels) {
                                    // Si el entero del label es igual al label, mostrar
                                    if (Math.floor(label) === label) {
                                        return label;
                                    }

                                },
                            }
                        }],
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: "Puntaje"
                            }
                        }]
                    },
                    responsive: false,
                    maintainAspectRatio: false,
                    legend: {
                        display: true
                    }
                },
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
			axios.get("/admin/ajax/usuarios/" + this.persona + "/evaluaciones-chartdata")
				.then(data => {
					this.chart.data = {
	                    labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
	                        datasets: [
	                            {
	                                label: 'Técnico',
	                                backgroundColor: '#82CFE8',
	                                data: data.data.cantidadesTecnico
	                            },
	                            {
	                                label: 'Social',
	                                backgroundColor: '#d3d3d3',
	                                data: data.data.cantidadesSocial
	                            }
	                        ]
                    };
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