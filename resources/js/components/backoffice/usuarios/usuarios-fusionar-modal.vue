<template>
	<div :class="{ 'modal':true, 'fade': true, 'in': display }" :style="{ 'display': display ? 'block' : 'none' }" id="inscribir-modal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="display = false" >
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Fusionar con otra cuenta</h4>
				</div>
				<div class="modal-body">
					<div :class="{ 'form-group': true, 'has-error': errors.idPersona }" >
						<label>Cuenta de donde traer los datos</label>
						<v-select 
							:options="personas" 
							@search="onSearch" 
							label="nombre" 
							v-model="personaSeleccionada" 
							:filterable="false"
							:selectOnTab="true"
							@search:focus="clear_error('idPersona')"
						>
							<template slot="no-options">Escribe el nombre, apellido o DNI</template>
						</v-select>
						<span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block" ></span>
					</div>

				</div>
				<div class="modal-footer">
					<button ref="cancelar" class="btn" @click="reset();personaSeleccionada=null;hide();personas=[];" >Cancelar</button>
					<button ref="inscribir" class="btn btn-primary" @click="submit" >Fusionar</button>
				</div>
			</div>
		</div>
		<simplert ref="confirmar"></simplert>
	</div>
</template>
<script>
import vSelect from 'vue-select';
import axios from 'axios';
import { debounce } from 'lodash';

export default {
	components: { 'v-select': vSelect },
	props: [ 'persona' ],
	data: function () {
		return {
			display: false,
			personas: [],
			personaSeleccionada: null,
			form: {
				idPersona: null
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
			this.display = true;
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
            this.$refs.confirmar.openSimplert({
                title: 'Fusionar cuentas',
                message: "Estás por fusionar esta cuenta de usuario con otra, si lo hacés, esta cuenta va a ser eliminada y todos sus datos reasignados a la cuenta elegida ¿Desea continuar?",
                useConfirmBtn: true,
                isShown: true,
                disableOverlayClick: true,
                customClass: 'confirmar',
                customCloseBtnText: 'CANCELAR',
                customCloseBtnClass: 'btn btn-default',
                customConfirmBtnText: 'Si, fusionar',
                customConfirmBtnClass: 'btn btn-primary',
                onConfirm: function () {
                    axios.post('/admin/usuarios/' + this.$parent.$props.persona + '/fusionar/' + this.$parent.form.idPersona)
						.then((datos) => {
							this.$parent.reset();
							this.$parent.personaSeleccionada=null; //para v-select
							this.$parent.hide();
							document.location.reload();
						})
						.catch((error) => { this.$parent.errors = error.response.data.errors; });
		                }
            })
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