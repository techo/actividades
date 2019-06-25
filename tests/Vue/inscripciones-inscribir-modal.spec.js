import { mount } from 'vue-test-utils';
import expect from 'expect';
import Vue from 'vue';
import axios from 'axios';
import moxios from 'moxios';
import vSelect from 'vue-select'
//import Test from '../../resources/assets/js/components/backoffice/actividades/inscripciones-inscribir-modal.vue';

let Test = Vue.component('inscripciones-inscribir-modal', {
	template: `
		<div>
			<v-select @search="onSearch" label="nombre" v-model="personaSeleccionada">
			</v-select>
		</div>
	`,
	components: { 'v-select': vSelect },
	data: function () {
		return {
			puntos: null,
			personas: null,
			personaSeleccionada: null,
		}
	},
	mounted() {
		axios.get('/admin/ajax/actividades/0/grupos')
			.then((datos) => {
				this.puntos = datos.data;
			})
			.catch((error) => { console.log(error) });
	},
	methods: {
		onSearch: function () {
			axios.get('/ajax/coordinadores?coordinador=arturo')
				.then((datos) => {
					this.personas = datos.data;
				})
				.catch((error) => { console.log(error) });
		}
	}
});


describe ('Se carga correctamente', () => {

	beforeEach(function () {
      // import and pass your custom axios instance to this method
      moxios.install()
    });

    afterEach(function () {
      // import and pass your custom axios instance to this method
      moxios.uninstall()
    });
	
	it ('Carga puntos de encuentro', (done) => {
		let wrapper = mount(Test);

		moxios.stubRequest('/admin/ajax/actividades/0/grupos', {
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

});