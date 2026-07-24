import { mount } from 'vue-test-utils';
import expect from 'expect';
import Vue from 'vue';

import Vuetable from '../../resources/js/components/backoffice/datatable/Vuetable.vue';

window.Event = new Vue();

// Regresión C-5: el wrapper Vuetable renderiza los campos comunes vía v-html.
// Los valores planos (datos de usuario, ej. nombre de un voluntario) deben escaparse
// para no ejecutar <script> en la sesión del backoffice; los campos marcados html:true
// (respuestas ya escapadas/armadas server-side) se renderizan como HTML confiable.
describe('Vuetable — escape de campos comunes (XSS)', () => {

	let vm = {};

	beforeEach(function () {
		const wrapper = mount(Vuetable, {
			propsData: {
				fields: [{ name: 'nombre' }, { name: 'pregunta_1', html: true }],
				apiMode: false,
				loadOnStart: false,
			},
			mocks: { $t: key => key, $tc: key => key },
		});
		vm = wrapper.vm;
	});

	it('escapa caracteres HTML peligrosos', () => {
		expect(vm.escapeHtml('<script>alert(1)</script>'))
			.toBe('&lt;script&gt;alert(1)&lt;/script&gt;');
		expect(vm.escapeHtml('a & "b" \'c\'')).toBe('a &amp; &quot;b&quot; &#039;c&#039;');
		expect(vm.escapeHtml(null)).toBe('');
	});

	it('escapa el valor de un campo común (dato de usuario)', () => {
		const html = vm.renderNormalField(
			{ name: 'nombre' },
			{ nombre: '<img src=x onerror=alert(1)>' }
		);
		expect(html).toBe('&lt;img src=x onerror=alert(1)&gt;');
	});

	it('NO re-escapa un campo marcado html:true (HTML confiable server-side)', () => {
		const html = vm.renderNormalField(
			{ name: 'pregunta_1', html: true },
			{ pregunta_1: '<a href="/x" target="_blank" rel="noopener">Ver archivo</a>' }
		);
		expect(html).toBe('<a href="/x" target="_blank" rel="noopener">Ver archivo</a>');
	});
});
