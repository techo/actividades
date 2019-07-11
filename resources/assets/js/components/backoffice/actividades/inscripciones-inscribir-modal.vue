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
						<v-select 
							:options="personas" 
							@search="onSearch" 
							label="nombre" 
							v-model="personaSeleccionada" 
							@search:focus="clear_error('idPersona')"
						>
							<template slot="no-options">Escribe el nombre, apellido o DNI</template>
						</v-select>
						<span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block" ></span>
					</div>

					<div :class="{ 'form-group': true, 'has-error': errors.idPuntoEncuentro }" >
						<label>Punto</label>
						<select 
							v-model="form.idPuntoEncuentro" 
							class="form-control" 
							@focus="clear_error('idPuntoEncuentro')"
						>
							<option v-for="punto in puntos" :value="punto.id" >{{ punto.punto }}</option>
						</select>
						<span v-if="errors.idPuntoEncuentro" v-text="errors.idPuntoEncuentro[0]" class="help-block" ></span>
					</div>

					<div class="form-group" >
						<div class="checkbox">
                            <label for="notificar">
                                <input type="checkbox" name="notificar" v-model="form.notificar">
                                Notificar a la persona
                            </label>
                        </div> 
					</div>

				</div>
				<div class="modal-footer">
					<button ref="cancelar" class="btn" @click="reset();personaSeleccionada=null;hide();personas=[];" >Cancelar</button>
					<button ref="inscribir" class="btn btn-primary" @click="submit" >Inscribir</button>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
import vSelect from 'vue-select';
import axios from 'axios';
import { debounce } from 'lodash';

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
				notificar: false
			},
			errors: {}
		}
	},
	mounted() {
		window.Event.$on('inscripciones:inscribir-button-clicked', this.show);
	},
	watch: {
		personaSeleccionada: function (v) {
			if(v) 
				this.form.idPersona = v.idPersona;
			else 
				this.form.idPersona = null;
		}
	},
	methods: {
		show: function () {  
			axios.get('/admin/ajax/actividades/' + this.idActividad + '/puntos')
				.then((datos) => { 
					this.puntos = datos.data;
					this.display = true;
				})
				.catch((error) => { console.log(error) });
		},
		hide: function () { 
			this.reset();
			this.personaSeleccionada=null; //para v-select
			this.display = false; 
		},
		onSearch: _.debounce( function (text, loading) {
			if(text.length > 3) {
				loading(true);
				axios.get('/ajax/coordinadores?coordinador=' + text)
					.then((datos) => { 
						this.personas = datos.data.data; 
						loading(false);
					})
					.catch((error) => { 
						console.log(error);
						loading(false);
					});
			}
		}, 400),
		submit: function () {

			axios.post('/admin/ajax/actividades/' + this.idActividad + '/inscripciones', this.form)
				.then((datos) => { 
					window.Event.$emit('inscripciones-actualizar-tabla');
					window.Event.$emit('mensaje-success', 'Persona inscripta');
					window.Event.$emit('vuetable-actualizarTabla');

					this.reset();
					this.personaSeleccionada=null; //para v-select
					this.hide(); 
				})
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
				this.errors[field] = null;
	    		delete this.errors[field];
	    	}
		},
		clear_error: function (field) {
	    	if (field) {
            	this.errors[field] = null;
            	delete this.errors[field];
	    	}
		}
	}
}
</script>