import { mount } from 'vue-test-utils';
import expect from 'expect';
import Vue from 'vue';
import axios from 'axios';
import moxios from 'moxios';
import vSelect from 'vue-select';
window.Event = new Vue();
import Test from '../../resources/assets/js/components/backoffice/actividades/inscripciones-inscribir-modal.vue';

describe ('Se puede inscribir un usuario a una actividad', () => {

	let wrapper = {};

	beforeEach(function () {
      // import and pass your custom axios instance to this method
      moxios.install()

      wrapper = mount(Test, { propsData: { idActividad: 1 }, });
    });

    afterEach(function () {
      // import and pass your custom axios instance to this method
      moxios.uninstall()
    });

    it ('Muestra el modal', () => {

		window.Event.$emit('inscripciones:inscribir-button-clicked')

		expect(wrapper.vm.display).toBe(true);
	});

	it ('Esconde el modal', () => {

		wrapper.find('button.close').trigger('click')

		expect(wrapper.vm.display).toBe(false);
	});
	
	it ('Carga puntos de encuentro', (done) => {

		moxios.stubRequest('/admin/ajax/actividades/' + wrapper.props().idActividad + '/puntos', {
	        status: 200,
	        response: [
	        	{"id":5513,"punto":"Palermo","pais":"Argentina"},
	        	{"id":5514,"punto":"Estaci\u00f3n Once"},
	        ]
      	});

		moxios.wait(function () {
			expect(wrapper.vm.puntos.length).toBe(2);
			expect(wrapper.vm.puntos[0].punto).toBe('Palermo');
			done();
		})
		
	});

	it ('Carga personas al escribir', (done) => {
		let select = wrapper.find(vSelect);

		moxios.stubRequest('/ajax/coordinadores?coordinador=arturo', {
	        status: 200,
	        response: { data: [
	        		{"idPersona":141032,"nombre":"Adrian Arturo Visbal Burgos  (visbal.adrian@ur.edu.co)"},
					{"idPersona":464078,"nombre":"Arturo  (arturomarvez@hotmail.com)"},
	        	]
	    	}
      	});

		select.vm.onSearchFocus();
      	select.vm.search = 'arturo';

		moxios.wait(function () {
			expect(wrapper.vm.personas.length).toBe(2);
			expect(wrapper.vm.personas[0].idPersona).toBe(141032);
			expect(wrapper.html()).toContain("Adrian Arturo Visbal Burgos  (visbal.adrian@ur.edu.co)");
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