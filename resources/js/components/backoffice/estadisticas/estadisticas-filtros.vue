<template>
	<form>
		<div class="col-md-2">
			<select class="form-control" v-model="pais_seleccionado" >
				 <option :value="null" selected >Todos</option>
				<option v-for="p in paises" :value="p.id">{{ p.nombre }}</option>
			</select>
		</div>
		<div class="col-md-2">
			<select class="form-control" v-model="oficina_seleccionada" >
				<option :value="null" selected >Todas</option>
				<option v-for="p in oficinas" :value="p.id">{{ p.nombre }}</option>
			</select>
		</div>
		<div class="col-md-2">
			<input class="form-control" placeholder="año" v-model="año_seleccionado" >
		</div>
		<div class="col-md-2">
			<button class="btn btn-primary" @click.prevent="filtrar()">Filtrar</button>
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
		        'año': this.año_seleccionado,
		      })
		}
	},
}
</script>