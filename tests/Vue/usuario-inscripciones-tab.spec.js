import { mount } from 'vue-test-utils';
import expect from 'expect';
import Vue from 'vue';
import axios from 'axios';
import moxios from 'moxios';

import Vuetable from 'vuetable-2/src/components/Vuetable'
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'

window.Event = new Vue();
import Test from '../../resources/assets/js/components/backoffice/usuarios/usuarios-inscripciones-tab.vue';

describe ('Pestaña de inscripciones de usuario', () => {

	let wrapper = {};

	beforeEach(function () {
		moxios.install()
		wrapper = mount(Test);
	});

	afterEach(function () {
		moxios.uninstall()
	});

	it ('muestra inscripciones del usuario al cargarse', (done) => {

		moxios.wait(function () {

			let request = moxios.requests.mostRecent();

			request.respondWith({
				status: 200,
				response: {
					"current_page": 1,
					"first_page_url": "http://localhost:8000/admin/ajax/usuarios/521623/inscripciones?page=1",
					"from": 1,
					"last_page": 4,
					"last_page_url": "http://localhost:8000/admin/ajax/usuarios/521623/inscripciones?page=4",
					"next_page_url": "http://localhost:8000/admin/ajax/usuarios/521623/inscripciones?page=2",
					"path": "http://localhost:8000/admin/ajax/usuarios/521623/inscripciones",
					"per_page": 10,
					"prev_page_url": null,
					"to": 10,
					"total": 31,
					"data": [{
						"nombreActividad": "Curso de Panadería Básica - Segundo Semestre 2011",
						"nombre": "Plan Educacion",
						"fechaInscripcion": "2011-09-01 00:00:00",
						"rol": "Voluntario",
						"estado": "Trabajando",
						"presente": null
					}],
				},
			}).then(function () {
				expect(wrapper.html()).toContain("Curso de Panadería Básica - Segundo Semestre 2011");
	        	done();
	        })
		})

	});

});