<template>
	<div class="box">
		<alert :mostrar="loading"></alert>
		<div class="box-header">
			<estadisticas-filtros :value="filtros" @input="filtros = arguments[0]; filtrar()" ></estadisticas-filtros>
		</div>
		<div class="box-body" >

			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li :class="{'active': display.actividades}"><a href="#actividades" data-toggle="tab" @click.prevent="display.inscriptos = false; display.actividades = true; display.evaluaciones = false;">Actividades</a></li>
					<li :class="{'active': display.inscriptos}"><a href="#inscriptos" data-toggle="tab" @click.prevent="display.inscriptos = true; display.actividades = false;display.evaluaciones = false;">Inscriptos</a></li>
					<li :class="{'active': display.evaluaciones}"><a href="#evaluaciones" data-toggle="tab" @click.prevent="display.inscriptos = false; display.evaluaciones = true; display.actividades = false;">Evaluaciones</a></li>
				</ul>
			</div>

			<div class="tab-content" style="min-height: 500px">
				<div id="inscriptos" class="tab-pane" :class="{'active': display.inscriptos}" >
					<div style="text-align: right">
						<button class="btn btn-default" @click="exportarInscriptos()" >Descargar Inscriptos <i class="fa fa-download"></i></button>
					</div>

					<div style="height: 200px" >
						<line-chart ref="graficoinscriptos" v-if="loaded.inscriptos" :chartData="dataInscriptos" :options="options"></line-chart>
					</div>
				</div>
				<div id="actividades" class="tab-pane" :class="{'active': display.actividades}">
					<div style="height: 200px" >
						<line-chart ref="graficoactividades" v-if="loaded.actividades" :chartData="dataActividades" :options="options"></line-chart>
					</div>
				</div>
				<div id="evaluaciones" class="tab-pane" :class="{'active': display.evaluaciones}" >
					<div style="text-align: right">
						<button class="btn btn-default" @click="exportarEvaluaciones()" >Descargar Evaluaciones <i class="fa fa-download"></i></button>
						<button class="btn btn-default" @click="exportarEvaluadores()" >Descargar Evaluadores <i class="fa fa-download"></i></button>
					</div>

					<div style="height: 200px" >
						<line-chart ref="graficoevaluaciones" v-if="loaded.evaluaciones" :chartData="dataEvaluaciones" :options="options"></line-chart>
					</div>
				</div>
			</div>

		</div>
	</div>
</template>

<script>
import LineChart from '../../plugins/LineChart';
import Alert from '../../plugins/Alert';
import EstadisticasFiltros from './estadisticas-filtros';

export default {
	components: { 
		'estadisticas-filtros': EstadisticasFiltros, 
		'line-chart': LineChart, 
		'alert': Alert },
	data() {
		return {
			filtros: {},
			display: {
				inscriptos: false,
				actividades: true,
				evaluaciones: false,
			},
			loaded: {
				inscriptos: false,
				actividades: false,
				evaluaciones: false,
			},
			loading: false,
			dataActividades: { labels: [], datasets: [] },
			dataInscriptos: { labels: [], datasets: [] },
			dataEvaluaciones: { labels: [], datasets: [] },
			options: {
				responsive: true,
				maintainAspectRatio: false,
				scales: {
	            xAxes: [{
		                ticks: {
		                    callback: function(value, index, values) {
		                        return moment(value, 'MM').format('MMMM');
		                    }
		                }
		            }]
		        }
		    }
		};
	},
	mounted () {
		this.datos_actividades();
	},
	computed: {
		inscriptos() {
			return this.display.inscriptos;
		},
		evaluaciones() {
			return this.display.evaluaciones;
		}
	},
	watch: {
		inscriptos (v) {
			if(v == true && this.loaded.inscriptos == false){
				this.display.evaluaciones = false;
				this.display.actividades = false;	
				this.datos_inscripciones();		
			}
		},
		evaluaciones (v) {
			if(v == true && this.loaded.evaluaciones == false){
				this.display.inscriptos = false;
				this.display.actividades = false;
				this.datos_evaluaciones();
			}
		},
	},
	methods: {
		filtrar () {
			if(this.display.actividades)
				this.datos_actividades();
			else if (this.display.inscriptos)
				this.datos_inscripciones();
			else
				this.datos_evaluaciones();
		},
		datos_actividades: function () {
			this.loading = true;
			this.dataActividades.labels = [];
			this.dataActividades.datasets = [];
			axios.get('/admin/ajax/estadisticas/grafico-actividades', { params: this.filtros } )
			.then((data) => {
				let i = 0;
				let labels = [];
				for (const key of Object.keys(data.data)) 
				{
					if(data.data.length == 0) {
						this.dataActividades.datasets = [];
						this.dataActividades.labels = [];
						this.loaded.actividades = true;
						this.loading = false;
						return;
					}

					labels = _.union(labels, data.data[key].labels);

					this.dataActividades.datasets[i] = {
			            label: key,
			            data: data.data[key].data,
			            borderWidth: 1,
			            lineTension: 0,
			            fill: false,
			            borderColor: data.data[key].color[0],
			            borderWidth: 6,
			        }
			        i++;
				};
				this.dataActividades.labels = _.sortBy(labels);
				this.loaded.actividades = true;
				this.loading = false;
				if(this.$refs.graficoactividades != undefined)
					this.$refs.graficoactividades.$data._chart.update()
			})
			.catch((error) => { debugger; })
		},
		datos_inscripciones: function () {
			this.loading = true;
			this.dataInscriptos.labels = [];
			this.dataInscriptos.datasets = [];
			axios.get('/admin/ajax/estadisticas/grafico-inscripciones', { params: this.filtros } )
			.then((data) => {
				if(data.data.length == 0) {
					this.dataInscriptos.datasets = [];
					this.dataInscriptos.labels = [];
					this.loaded.inscriptos = true;
					this.loading = false;
				}

				this.dataInscriptos.labels = data.data.meses;

				this.dataInscriptos.datasets[0] = {
		            label: 'Inscriptos',
		            data: data.data.inscriptos,
		            borderWidth: 1, lineTension: 0, fill: false, borderWidth: 6
			    };
			    this.dataInscriptos.datasets[1] = {
		            label: 'Presentes',
		            data: data.data.presentes,
		            borderColor: '#4ac0c1',
		            borderWidth: 1, lineTension: 0, fill: false, borderWidth: 6
			    }
				this.loaded.inscriptos = true;
				this.loading = false;
				if(this.$refs.graficoinscriptos != undefined)
					this.$refs.graficoinscriptos.$data._chart.update()
			})
			.catch((error) => { debugger; });
		},
		datos_evaluaciones: function () {
			this.loading = true;
			this.dataEvaluaciones.labels = [];
			this.dataEvaluaciones.datasets = [];
			axios.get('/admin/ajax/estadisticas/grafico-evaluaciones', { params: this.filtros } )
			.then((data) => {
				if(data.data.length == 0) {
					this.dataEvaluaciones.datasets = [];
					this.dataEvaluaciones.labels = [];
					this.loaded.evaluaciones = true;
					this.loading = false;
				}
				this.dataEvaluaciones.labels = data.data.meses;

				this.dataEvaluaciones.datasets[0] = {
		            label: 'Sociales',
		            data: data.data.puntajeSocial,
		            borderWidth: 1, lineTension: 0, fill: false, borderWidth: 6
			    };
			    this.dataEvaluaciones.datasets[1] = {
		            label: 'Técnicas',
		            data: data.data.puntajeTecnico,
		            borderColor: '#4ac0c1',
		            borderWidth: 1, lineTension: 0, fill: false, borderWidth: 6
			    }
				this.loaded.evaluaciones = true;
				this.loading = false;
				if(this.$refs.graficoevaluaciones != undefined)
					this.$refs.graficoevaluaciones.$data._chart.update()
			})
			.catch((error) => { debugger; });
		},
		exportarInscriptos: function() {
			this.loading = true;
			//https://gist.github.com/javilobo8/097c30a233786be52070986d8cdb1743
			axios({
				url: '/admin/ajax/estadisticas/inscripciones/exportar', 
				params: this.filtros,
				responseType: 'blob',
				headers: { 
					'Accept': 'application/octet-stream',
					'content-type': 'application/vnd.ms-excel;charset=UTF-8',
				}
			})
			.then((response) => {
				const url = window.URL.createObjectURL(new Blob([response.data]));
				const link = document.createElement('a');
				link.href = url;
				link.setAttribute('download', 'Insciptos.xlsx');
				document.body.appendChild(link);
				link.click();
				this.loading = false;
			})
			.catch((error) => { debugger; });
		},
		exportarEvaluaciones: function() {
			this.loading = true;
			//https://gist.github.com/javilobo8/097c30a233786be52070986d8cdb1743
			axios({
				url: '/admin/ajax/estadisticas/evaluaciones/exportar', 
				params: this.filtros,
				responseType: 'blob',
				headers: { 
					'Accept': 'application/octet-stream',
					'content-type': 'application/vnd.ms-excel;charset=UTF-8',
				}
			})
			.then((response) => {
				const url = window.URL.createObjectURL(new Blob([response.data]));
				const link = document.createElement('a');
				link.href = url;
				link.setAttribute('download', 'evaluacionesGenerales.xlsx');
				document.body.appendChild(link);
				link.click();
				this.loading = false;
			})
			.catch((error) => { debugger; });
		},
		exportarEvaluadores: function() {
			this.loading = true;
			//https://gist.github.com/javilobo8/097c30a233786be52070986d8cdb1743
			axios({
				url: '/admin/ajax/estadisticas/evaluadores/exportar', 
				params: this.filtros,
				responseType: 'blob',
				headers: { 
					'Accept': 'application/octet-stream',
					'content-type': 'application/vnd.ms-excel;charset=UTF-8',
				}
			})
			.then((response) => {
				const url = window.URL.createObjectURL(new Blob([response.data]));
				const link = document.createElement('a');
				link.href = url;
				link.setAttribute('download', 'evaluadoresGenerales.xlsx');
				document.body.appendChild(link);
				link.click();
				this.loading = false;
			})
			.catch((error) => { debugger; });
		},
	},
}
</script>