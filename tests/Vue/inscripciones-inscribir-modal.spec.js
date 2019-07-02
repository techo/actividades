import { mount } from 'vue-test-utils';
import expect from 'expect';
import Vue from 'vue';
import axios from 'axios';
import moxios from 'moxios';
import vSelect from 'vue-select';
window.Event = new Vue();
import Test from '../../resources/assets/js/components/backoffice/actividades/inscripciones-inscribir-modal.vue';

describe ('Modal de inscripción', () => {

	let wrapper = {};

	beforeEach(function () {
      moxios.install()
      wrapper = mount(Test, { propsData: { idActividad: 1 }, });
    });

    afterEach(function () {
      moxios.uninstall()
    });

    it ('muesta con los puntos de encuentro', (done) => {

    	moxios.stubRequest('/admin/ajax/actividades/' + wrapper.props().idActividad + '/puntos', {
	        status: 200,
	        response: [
	        	{"id":5513,"punto":"Palermo","pais":"Argentina"},
	        	{"id":5514,"punto":"Estaci\u00f3n Once"},
	        ]
      	});

		window.Event.$emit('inscripciones:inscribir-button-clicked')

		moxios.wait(function () {
			expect(wrapper.find('#inscribir-modal').hasStyle('display', 'block')).toBe(true);
			expect(wrapper.findAll('option').length).toBe(2);
			expect(wrapper.findAll('option').wrappers[0].element.value).toBe("5513");
			done();
		})
	});

	it ('carga con personas al escribir', (done) => {
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
				expect(wrapper.html()).toContain("Adrian Arturo Visbal Burgos  (visbal.adrian@ur.edu.co)");
				expect(wrapper.html()).toContain("Arturo  (arturomarvez@hotmail.com)");
				done();
			}, 400)
		})
		
	});

	it ('envia datos, emite evento, limpia el form y cierra el modal al confirmar', (done) => {
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
	        	expect(wrapper.find('#inscribir-modal').hasStyle('display', 'none')).toBe(true);
	        	expect(wrapper.findAll('div.has-error').length).toBe(0);
	        	//debería chequear que emita los eventos a el dispatcher window.Event
				done();
	        })
		})

	})

	it ('muestra errores', (done) => {

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

	it ('limpia errores al hacer foco en un campo', (done) => {
		
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
				wrapper.find(vSelect).vm.onSearchFocus();
				wrapper.find('select').trigger('focus');
				expect(wrapper.findAll('span.help-block').length).toBe(0);
				expect(wrapper.findAll('div.has-error').length).toBe(0);
				done();
			});

		})

	})

	it ('limpia el formulario y los errores al apretar Cancelar', (done) => {

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
				wrapper.find({ref: 'cancelar'}).trigger('click');
				expect(wrapper.findAll('span.help-block').length).toBe(0);
				expect(wrapper.findAll('div.has-error').length).toBe(0);
				done();
			});

		})

	})

});