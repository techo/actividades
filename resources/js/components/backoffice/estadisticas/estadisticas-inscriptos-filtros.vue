<template>
	<form>
		<div class="form-inline">
				<h5 class="form-control" style="border:0">{{ $t('backend.office') }}</h5>
				<select class="form-control" v-model="oficina_seleccionada" >
					<option :value="null" selected >{{ $t('backend.all') }}</option>
					<option v-for="p in oficinas" :value="p.id">{{ p.nombre }}</option>
				</select>
				<h5 class="form-control" style="border:0">{{ $t('backend.date') }}</h5>
				<input v-model="fecha_desde" type="date" class="form-control">
				<input v-model="fecha_hasta" type="date" class="form-control">	
				<h5 class="form-control" style="border:0">{{ $t('backend.age') }}</h5>
				<input v-model="edad_desde" type="number" class="form-control">
				<input v-model="edad_hasta" type="number" class="form-control">
			<button class="btn btn-primary form-control" @click.prevent="filtrar()">{{ $t('backend.filter') }}</button>
			<button class="btn btn-secondary form-control" @click="exportarPersonasInscriptas">{{ $t('backend.download') }}</button>
		</div>
	</form>
</template>

<script>
import LineChart from '../../plugins/LineChart';
import Simplert from 'vue2-simplert';

export default {
	components: { 'line-chart': LineChart, 'simplert': Simplert },
	data() {
		return {
			paises: [],
			oficinas: [],
			pais_seleccionado: null,
			oficina_seleccionada: null,
			año_seleccionado: moment().format('YYYY'),
			fecha_desde: moment().startOf('year').format('YYYY-MM-DD'),
			fecha_hasta: moment().format('YYYY-MM-DD'),
			edad_desde: 0,
			edad_hasta: 90,
		};
	},
	mounted () {
		axios.get('/admin/ajax/paises').then((data) => { this.paises = data.data; });
		axios.get('/admin/ajax/oficinas').then((data) => { this.oficinas = data.data; });
	},
	methods: {
		filtrar () {
			this.$emit('input', {
		        'pais': this.pais_seleccionado,
		        'oficina': this.oficina_seleccionada,
		        'fecha_desde': this.fecha_desde,
		        'fecha_hasta': this.fecha_hasta,
		        'edad_desde': this.edad_desde,
		        'edad_hasta': this.edad_hasta,
		        'año': this.año_seleccionado,
		      })
		},
		exportarPersonasInscriptas: function() {
			this.loading = true;
			let filtros = {
		        'pais': this.pais_seleccionado,
		        'oficina': this.oficina_seleccionada,
		        'fecha_desde': this.fecha_desde,
		        'fecha_hasta': this.fecha_hasta,
		        'edad_desde': this.edad_desde,
		        'edad_hasta': this.edad_hasta,
		        'año': this.año_seleccionado,
		      }
			axios({
				url: '/admin/ajax/estadisticas/inscripciones/personas/exportar', 
				params: filtros,
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
	},
}
</script>