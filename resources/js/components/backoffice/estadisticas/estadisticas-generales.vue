<template>
	<div class="box">
		<simple-alert ref="loading"></simple-alert>
		<div class="box-header">
			<estadisticas-filtros :value="filtros" @input="filtros = arguments[0]; filtrar()" ></estadisticas-filtros>
		</div>
		<div class="box-body" >

			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li :class="{'active': display.actividades}"><a href="#actividades" data-toggle="tab" @click.prevent="display.inscriptos = false; display.actividades = true;">Actividades</a></li>
					<li :class="{'active': display.inscriptos}"><a href="#inscriptos" data-toggle="tab" @click.prevent="display.inscriptos = true; display.actividades = false;">Inscriptos</a></li>
				</ul>
			</div>

			<div class="tab-content" style="min-height: 500px">
				<div id="inscriptos" class="tab-pane" :class="{'active': display.inscriptos}" >
					<div style="height: 200px" >
						<line-chart ref="graficoinscriptos" v-if="loaded.inscriptos" :chartData="dataInscriptos" :options="options"></line-chart>
					</div>
				</div>
				<div id="actividades" class="tab-pane" :class="{'active': display.actividades}">
					<div style="height: 200px" >
						<line-chart ref="graficoactividades" v-if="loaded.actividades" :chartData="dataActividades" :options="options"></line-chart>
					</div>
				</div>
			</div>

		</div>
	</div>
</template>

<script>
import LineChart from '../../plugins/LineChart';
import EstadisticasFiltros from './estadisticas-filtros';
import Simplert from 'vue2-simplert';

export default {
	components: { 
		'estadisticas-filtros': EstadisticasFiltros, 
		'line-chart': LineChart, 
		'simplert': Simplert },
	data() {
		return {
			filtros: {},
			display: {
				inscriptos: false,
				actividades: true,
			},
			loaded: {
				inscriptos: false,
				actividades: false,
			},
			dataActividades: { labels: [], datasets: [] },
			dataInscriptos: { labels: [], datasets: [] },
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
		}
	},
	watch: {
		inscriptos (v) {
			if(v == true && this.loaded.inscriptos == false)
				this.datos_inscripciones();
		},
	},
	methods: {
		filtrar () {
			if(this.display.actividades)
				this.datos_actividades();
			else
				this.datos_inscripciones();
		},
		datos_actividades: function () {
			this.mostrarLoadingAlert();
			this.dataActividades.labels = [];
			this.dataActividades.datasets = [];
			axios.get('/admin/ajax/estadisticas/actividades', { params: this.filtros } )
			.then((data) => {
				let i = 0;
				let labels = [];
				for (const key of Object.keys(data.data)) 
				{
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
				this.$refs.loading.justCloseSimplert();
				if(this.$refs.graficoactividades != undefined)
					this.$refs.graficoactividades.$data._chart.update()
			})
			.catch((error) => { debugger; })
		},
		datos_inscripciones: function () {
			this.mostrarLoadingAlert();
			this.dataInscriptos.labels = [];
			this.dataInscriptos.datasets = [];
			axios.get('/admin/ajax/estadisticas/inscripciones', { params: this.filtros } )
			.then((data) => { 
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
				this.$refs.loading.justCloseSimplert();
				if(this.$refs.graficoinscriptos != undefined)
					this.$refs.graficoinscriptos.$data._chart.update()
			})
			.catch((error) => { debugger; });
		},
		mostrarLoadingAlert() {
			this.$refs.loading.openSimplert({
				title: 'Espera...',
				message: "<i class=\"fa fa-spinner fa-spin fa-4x\"></i>",
				hideAllButton: true,
				isShown: true,
				disableOverlayClick: true,
				type: ''
			})
		}
	},
}
</script>