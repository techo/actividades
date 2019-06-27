import { shallowMount, mount } from 'vue-test-utils';
import expect from 'expect';
import Vue from 'vue';
import axios from 'axios';
import moxios from 'moxios';
import vSelect from 'vue-select'
//import Test from '../../resources/assets/js/components/backoffice/actividades/inscripciones-inscribir-modal.vue';

let Test = Vue.component('inscripciones-inscribir-modal', {
	template: `
		<div>
			<div :class="{ 'form-group': true, 'has-error': errors.idPersona }" >
				<v-select @search="onSearch" label="nombre" v-model="personaSeleccionada">
				</v-select>
				<span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block" ></span>
			</div>

			<div :class="{ 'form-group': true, 'has-error': errors.idPuntoEncuentro }" >
				<select v-model="form.idPuntoEncuentro">
					<option v-for="punto in puntos" :value="punto.id" >{{ punto.punto }}</option>
				</select>
				<span v-if="errors.idPuntoEncuentro" v-text="errors.idPuntoEncuentro[0]" class="help-block" ></span>
			</div>

			<button ref="inscribir" class="btn btn-primary" @click="submit" >Inscribir</button>
			<button ref="cancelar" class="btn" @click="reset" >Cancelar</button>
		</div>
	`,
	components: { 'v-select': vSelect },
	props: [ 'idActividad' ],
	data: function () {
		return {
			puntos: null,
			personas: null,
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
		axios.get('/admin/ajax/actividades/' + this.idActividad + '/grupos')
			.then((datos) => { this.puntos = datos.data; })
			.catch((error) => { console.log(error) });
	},
	watch: {
		personaSeleccionada: function (v) {
			if(v) this.form.idPersona = v.idPersona;
		}
	},
	methods: {
		onSearch: function (text, loading) {
			axios.get('/ajax/coordinadores?coordinador=' + text)
				.then((datos) => { this.personas = datos.data; })
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
});


describe ('Se puede inscribir un usuario a una actividad', () => {

	beforeEach(function () {
      // import and pass your custom axios instance to this method
      moxios.install()
    });

    afterEach(function () {
      // import and pass your custom axios instance to this method
      moxios.uninstall()
    });
	
	it ('Carga puntos de encuentro', (done) => {
		let wrapper = mount(Test, {
			propsData: {
				idActividad: 1
			}
		});

		moxios.stubRequest('/admin/ajax/actividades/' + wrapper.props().idActividad + '/grupos', {
	        status: 200,
	        response: [
	        	{"id":5513,"punto":"Palermo","pais":"Argentina","provincia":"Capital Federal","localidad":"Palermo","nombres":"Ornella","apellidoPaterno":"Morbelli"},
	        	{"id":5514,"punto":"Estaci\u00f3n Once","pais":"Argentina","provincia":"Capital Federal","localidad":"Balvanera","nombres":"Renata","apellidoPaterno":"Barriopedro"},
	        ]
      	});

		moxios.wait(function () {
			expect(wrapper.vm.puntos.length).toBe(2);
			expect(wrapper.vm.puntos[0].punto).toBe('Palermo');
			done();
		})
		
	});

	it ('Carga personas al escribir', (done) => {
		let wrapper = mount(Test);
		let select = wrapper.find(vSelect);

		moxios.stubRequest('/ajax/coordinadores?coordinador=arturo', {
	        status: 200,
	        response: [
	        	{"idPersona":141032,"dni":"","nombre":"Adrian Arturo Visbal Burgos  (visbal.adrian@ur.edu.co)","nombres":"Adrian Arturo Visbal Burgos","apellidoPaterno":""},
				{"idPersona":464078,"dni":"1-1690-0775","nombre":"Arturo  (arturomarvez@hotmail.com)","nombres":"Arturo","apellidoPaterno":""},
	        ]
      	});

      	select.vm.search = 'arturo';

		moxios.wait(function () {
			expect(wrapper.vm.personas.length).toBe(2);
			expect(wrapper.vm.personas[0].idPersona).toBe(141032);
			done();
		})
		
	});

	it ('Elije opciones y envia', (done) => {
		let wrapper = mount(Test, {
			propsData: {
				idActividad: 1
			},
			data: () => ({
				personaSeleccionada: null, 
				personas: [
      				{"idPersona":141032,"nombre":"Adrian Arturo Visbal Burgos  (visbal.adrian@ur.edu.co)"},
					{"idPersona":464078,"nombre":"Arturo  (arturomarvez@hotmail.com)"}
				],
				puntos: [
					{"id":5513,"punto":"Palermo"},
	        		{"id":5514,"punto":"Estaci\u00f3n Once"},
				] 
			}),
		});

		moxios.stubRequest('POST', '/admin/ajax/actividades/' + wrapper.props().idActividad + '/inscripciones', {
	        status: 200,
	        response: [ { mensaje: 'ok' }]
      	});

      	//elegir opciones
      	//para un html select
      	let options = wrapper.find('select').findAll('option');
      	options.at(0).element.selected = true;
      	wrapper.find('select').trigger('change');
      	//para componente v-select
      	wrapper.vm.$children[0].select({"idPersona":464078,"nombre":"Arturo  (arturomarvez@hotmail.com)"});

      	// click en enviar
		wrapper.find('button.btn-primary').trigger('click');
		
		// assert request enviado
		moxios.wait(function () {
			//console.log(moxios.requests.mostRecent());

			expect(wrapper.vm.form.idPersona).toBe(464078);
			expect(wrapper.vm.form.idPuntoEncuentro).toBe(5513);

			let request = moxios.requests.mostRecent()
	        
	        request.respondWith({
				status: 200,
				response: 'ok'
	        }).then(function () {
	        	expect(wrapper.emitted()["mensaje:ok"]).toBeTruthy();
				done();
	        })
		})

	})

	it ('Muestra error para punto de encuentro', (done) => {
		let wrapper = mount(Test, {
			propsData: { idActividad: 1 },
			data: () => ({ form: { idPersona: 141032, } }),
		});

		wrapper.find('button.btn-primary').trigger('click');
		
		moxios.wait(function () {
			let request = moxios.requests.mostRecent();

			request.respondWith({
				status: 422,
				response: { errors: { idPuntoEncuentro: ['El campo idPuntoEncuentro es requerido.'] } }
			}).then(function () {
				expect(wrapper.findAll('span.help-block').length).toBe(1);
				expect(wrapper.findAll('div.has-error').length).toBe(1);
				done();
			});

		})

	})

	it ('Muestra error para persona', (done) => {
		let wrapper = mount(Test, {
			propsData: { idActividad: 1 },
			data: () => ({ form: { idPuntoEncuentro: 5514, } }),
		});

		wrapper.find('button.btn-primary').trigger('click');
		
		moxios.wait(function () {
			let request = moxios.requests.mostRecent();

			request.respondWith({
				status: 422,
				response: { errors: { idPersona: ['El campo idPersona es requerido.'] } }
			}).then(function () {
				expect(wrapper.findAll('span.help-block').length).toBe(1);
				expect(wrapper.findAll('div.has-error').length).toBe(1);
				done();
			});

		})

	})

	it ('Muestra todos los errores', (done) => {
		let wrapper = mount(Test, {
			propsData: { idActividad: 1 },
			data: () => ({ form: {} }),
		});

		wrapper.find('button.btn-primary').trigger('click');
		
		moxios.wait(function () {
			let request = moxios.requests.mostRecent();

			request.respondWith({
				status: 422,
				response: { errors: { 
					idPersona: ['El campo idPersona es requerido.'],
					idPuntoEncuentro: ['El campo idPuntoEncuentro es requerido.']
				} }
			}).then(function () {
				expect(wrapper.findAll('span.help-block').length).toBe(2);
				expect(wrapper.findAll('div.has-error').length).toBe(2);
				done();
			});

		})

	})

	it ('Botón cancelar limpia el formulario y los errores', () => {
		let wrapper = mount(Test, {
			propsData: { idActividad: 1 },
			data: () => ({ 
				form: {
					idPuntoEncuentro: 1,
					idPersona: 1,
				},
				errors: { 
					idPersona: ['El campo idPersona es requerido.'],
					idPuntoEncuentro: ['El campo idPuntoEncuentro es requerido.']
				}
			}),
		});

		//click en cancelar
		wrapper.find({ref: 'cancelar'}).trigger('click');

		//limpia formulario
		expect(wrapper.find(vSelect).vm.value).toBe(null);
		expect(wrapper.find('select').element.value).toBe("");

		//limpia errores
		expect(wrapper.findAll('span.help-block').length).toBe(0);
		expect(wrapper.findAll('div.has-error').length).toBe(0);

	})

});