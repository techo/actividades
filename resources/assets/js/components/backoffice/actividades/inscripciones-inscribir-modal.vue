<template>
	<div :class="{ 'modal':true, 'fade': true, 'in': display }" :style="{ 'display': display ? 'block' : 'none' }" id="inscribir-modal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="display = false" >
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Inscribir</h4>
				</div>
				<div class="modal-body">
					<div :class="{ 'form-group': true, 'has-error': errors.idPersona }" >
						<label>Persona</label>
						<v-select :options="personas" @search="onSearch" label="nombre" v-model="personaSeleccionada">
						</v-select>
						<span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block" ></span>
					</div>

					<div :class="{ 'form-group': true, 'has-error': errors.idPuntoEncuentro }" >
						<label>Punto</label>
						<select v-model="form.idPuntoEncuentro" class="form-control">
							<option v-for="punto in puntos" :value="punto.id" >{{ punto.punto }}</option>
						</select>
						<span v-if="errors.idPuntoEncuentro" v-text="errors.idPuntoEncuentro[0]" class="help-block" ></span>
					</div>

				</div>
				<div class="modal-footer">
					<button ref="cancelar" class="btn" @click="reset" >Cancelar</button>
					<button ref="inscribir" class="btn btn-primary" @click="submit" >Inscribir</button>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import vSelect from 'vue-select';
import axios from 'axios';

export default {
	components: { 'v-select': vSelect },
	props: [ 'idActividad' ],
	data: function () {
		return {
			display: false,
			puntos: null,
			personas: [],
			personaSeleccionada: null,
			form: {
				idPersona: null,
				idPuntoEncuentro: null,
				//idActividad: 18029
				//idGrupo: 266
				//rol: ''
			},
			errors: {}
		}
	},
	mounted() {
		axios.get('/admin/ajax/actividades/' + this.idActividad + '/puntos')
			.then((datos) => { this.puntos = datos.data; })
			.catch((error) => { console.log(error) });
		window.Event.$on('inscripciones:inscribir-button-clicked', this.show);
	},
	watch: {
		personaSeleccionada: function (v) {
			if(v) this.form.idPersona = v.idPersona;
		}
	},
	methods: {
		show: function () { this.display = true; },
		hide: function () { this.display = false; },
		onSearch: function (text, loading) {
			axios.get('/ajax/coordinadores?coordinador=' + text)
				.then((datos) => { this.personas = datos.data.data; })
				.catch((error) => { console.log(error) });
			loading(false);
		},
		submit: function () {
			//console.log("submit!")
			axios.post('/admin/ajax/actividades/' + this.idActividad + '/inscripciones')
				.then((datos) => { this.$emit("mensaje:ok"); })
				.catch((error) => { this.errors = error.response.data.errors; });
		},
		reset: function () {
	    	for (let field in this.form) {
	    		this.form[field] = null;
	    	}
	    	this.reset_errors();
		},
		reset_errors: function () {
			for (let field in this.errors) {
	    		delete this.errors[field];
	    	}
		}
	}
}
</script>