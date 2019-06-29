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
      moxios.install()
      wrapper = mount(Test, { propsData: { idActividad: 1 }, });
    });

    afterEach(function () {
      moxios.uninstall()
    });

    it ('Muestra el modal con los puntos de encuentro', (done) => {

    	moxios.stubRequest('/admin/ajax/actividades/' + wrapper.props().idActividad + '/puntos', {
	        status: 200,
	        response: [
	        	{"id":5513,"punto":"Palermo","pais":"Argentina"},
	        	{"id":5514,"punto":"Estaci\u00f3n Once"},
	        ]
      	});

		window.Event.$emit('inscripciones:inscribir-button-clicked')

		moxios.wait(function () {
			expect(wrapper.vm.display).toBe(true);
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
			setTimeout(function () {
				expect(wrapper.vm.personas.length).toBe(2);
				expect(wrapper.vm.personas[0].idPersona).toBe(141032);
				expect(wrapper.html()).toContain("Adrian Arturo Visbal Burgos  (visbal.adrian@ur.edu.co)");
				done();
			}, 400)
		})
		
	});

	it ('Inscribir envia datos, emite evento, limpia el form y cierra el modal', (done) => {
		wrapper.setData({
				display: true,
				personaSeleccionada: null, 
				personas: [
      				{"idPersona":141032,"nombre":"Adrian Arturo Visbal Burgos  (visbal.adrian@ur.edu.co)"},
					{"idPersona":464078,"nombre":"Arturo  (arturomarvez@hotmail.com)"}
				],
				puntos: [
					{"id":5513,"punto":"Palermo"},
	        		{"id":5514,"punto":"Estaci\u00f3n Once"},
				] 
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

			let request = moxios.requests.mostRecent()
	        
	        request.respondWith({
				status: 200,
				response: 'ok'
	        }).then(function () {
	        	expect(request.config.data).toContain(464078);
	        	expect(request.config.data).toContain(5513);
	        	expect(wrapper.vm.display).toBe(false);
	        	expect(wrapper.vm.errors).toStrictEqual({});
	        	//debería chequear que emita los eventos a el dispatcher window.Event
				done();
	        })
		})

	})

	it ('Muestra error para punto de encuentro', (done) => {

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

	it ('Foco en un campo limpia errores', () => {
		wrapper.setData({
			personaSeleccionada: 1,
			form: {
				idPuntoEncuentro: 1,
			},
			errors: { 
				idPersona: ['El campo idPersona es requerido.'],
				idPuntoEncuentro: ['El campo idPuntoEncuentro es requerido.']
			}
		})

		wrapper.find(vSelect).vm.onSearchFocus();
		wrapper.find('select').trigger('focus');

		//console.log(wrapper.vm.errors)

		expect(wrapper.findAll('span.help-block').length).toBe(0);
		expect(wrapper.findAll('div.has-error').length).toBe(0);

	})

	it ('Botón cancelar limpia el formulario y los errores', () => {

		wrapper.setData({
			personaSeleccionada: 1,
			form: {
				idPuntoEncuentro: 1,
			},
			errors: { 
				idPersona: ['El campo idPersona es requerido.'],
				idPuntoEncuentro: ['El campo idPuntoEncuentro es requerido.']
			}
		})

		//click en cancelar
		wrapper.find({ref: 'cancelar'}).trigger('click');

		//limpia formulario
		expect(wrapper.vm.personaSeleccionada).toBe(null);
		expect(wrapper.vm.form.idPuntoEncuentro).toBe(null);

		//limpia errores
		expect(wrapper.findAll('span.help-block').length).toBe(0);
		expect(wrapper.findAll('div.has-error').length).toBe(0);

	})

});