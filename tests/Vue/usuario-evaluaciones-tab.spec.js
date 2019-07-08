import { mount } from 'vue-test-utils';
import expect from 'expect';
import Vue from 'vue';
import axios from 'axios';
import moxios from 'moxios';
import moment from 'moment';
window.moment = moment;

import Vuetable from 'vuetable-2/src/components/Vuetable'
import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'

window.Event = new Vue();
import Test from '../../resources/assets/js/components/backoffice/usuarios/usuarios-evaluaciones-tab.vue';
import BarChart from '../../resources/assets/js/components/plugins/BarChart.vue';

describe ('Pestaña de evaluaciones de usuario', () => {

	let wrapper = {};

	beforeEach(function () {
		moxios.install()
		wrapper = mount(Test);
	});

	afterEach(function () {
		moxios.uninstall()
	});

	it ('muestra evaluaciones del usuario al cargarse', (done) => {

		moxios.wait(function () {

			let request = moxios.requests.first();

			request.respondWith({
				status: 200,
				response: {
					"current_page": 1,
					"first_page_url": "http://localhost:8000/admin/ajax/usuarios/521623/evaluaciones?page=1",
					"from": 1,
					"last_page": 4,
					"last_page_url": "http://localhost:8000/admin/ajax/usuarios/521623/evaluaciones?page=4",
					"next_page_url": "http://localhost:8000/admin/ajax/usuarios/521623/evaluaciones?page=2",
					"path": "http://localhost:8000/admin/ajax/usuarios/521623/evaluaciones",
					"per_page": 10,
					"prev_page_url": null,
					"to": 10,
					"total": 31,
					"data": [{
						"nombreActividad": "Construcción MAYO- Sede La Plata",
						"nombre": "Construcción",
						"fechaInicio": "2019-05-24 20:00:00",
						"puntajeSocial": 10,
						"puntajeTecnico": 10,
						"comentario": null
					}],
				},
			}).then(function () {
				expect(wrapper.html()).toContain("Construcción MAYO- Sede La Plata");
	        	done();
	        }) 
		})

	});

	it ('carga datos histogramas', (done) => {

		moxios.wait(function () {

			let request = moxios.requests.mostRecent();

			request.respondWith({
				status: 200,
				response: {
					"cantidadesSocial":[0,0,0,0,2,0,9,3,2,37],
					"cantidadesTecnico":[0,0,0,0,4,0,7,1,3,14,4,20]
				},
			}).then(function () {
				expect(wrapper.find(BarChart)).not.toBe(false);
	        	done();
	        }) 
		})

	});

});