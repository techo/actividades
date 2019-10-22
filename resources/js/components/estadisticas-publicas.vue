<template>
	<div class="estadisticas-publicas">
			<div> 
				<h3>  {{ voluntades_movilizadas }} </h3>  
				<span>Voluntades Movilizadas</span>
			</div>
			
			<div> 
				<h3>  {{ personas_movilizadas.length }} </h3> 
				<span>Personas Movilizadas</span>
			</div>

			<template v-for="actividadtotal in actividadestotales">
				<div> 
					<h3> {{ actividadtotal.cantidad }} </h3> 
					<span> {{ actividadtotal.nombre }} </span>
				</div>
			</template>
	</div>
</template>

<script>
export default {
	data() {
		return {
			personas_movilizadas: 0,
			voluntades_movilizadas: 0,
			actividadestotales: 0,
		};
	},
	mounted () {
		this.get_voluntades_movilizadas();
		this.get_actividades();
		this.get_personas_movilizadas();
	},
	methods: {
		get_personas_movilizadas: function () {
			axios.get('/ajax/estadisticas/personas_movilizadas' )
			.then(response => (this.personas_movilizadas = response.data))
		},
		get_voluntades_movilizadas: function () {
			axios.get('/ajax/estadisticas/voluntades_movilizadas' )
			.then(response => (this.voluntades_movilizadas = response.data))
		},
		get_actividades: function () {
			axios.get('/ajax/estadisticas/actividades' )
			.then(response => (this.actividadestotales = response.data))
		},
	}
}

</script>