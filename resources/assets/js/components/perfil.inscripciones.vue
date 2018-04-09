<template>
	<div>
		<div class="row" v-for="actividad in actividades">
			<div class="col-md-7">
				<a :href="'/actividades/' + actividad.idActividad">{{actividad.nombreActividad}}</a>
			</div>
			<div class="col-md-3">
				<a class="btn btn-success" @click="desincribir(actividad.idActividad)">Desinscribirme</a>
			</div>
		</div>
	</div>
</template>

<script>
export default {
  name: 'perfilinscripciones',
  data: function() {
  	return {
  		actividades: []
  	}
  },
  mounted: function() {
  	this.traer_inscripciones()
  },
  methods: {
  	desincribir: function (idActividad) {
  		axios.delete('/ajax/usuario/inscripciones/' + idActividad).then(response => {
  			this.traer_inscripciones()
  		})
  	},
  	traer_inscripciones: function () {
	  	axios.get('/ajax/usuario/inscripciones').then(response => {
	  		this.actividades = response.data
	  	})
  	}
  }
}
</script>