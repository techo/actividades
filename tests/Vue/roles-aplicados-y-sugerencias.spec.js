import { mount } from 'vue-test-utils';
import expect from 'expect';
import Vue from 'vue';
import moxios from 'moxios';

import RolesAplicados from '../../resources/js/components/backoffice/datatable/RolesAplicadosField.vue';
import PreguntasManager from '../../resources/js/components/backoffice/preguntas/PreguntasManager.vue';

window.Event = window.Event || new Vue();

const mocks = { $t: key => key, $te: () => false, $tc: key => key };

// Reclamo #9: columna "Roles a los que aplicó" con confirmación en un click.
describe('RolesAplicadosField', () => {
	beforeEach(() => moxios.install());
	afterEach(() => moxios.uninstall());

	const fila = (extra) => Object.assign({
		id: 10, idActividad: 99, nombreRol: 'monitor',
		roles_aplicados: '["monitor","intendencia"]',
	}, extra);

	it('muestra los roles aplicados (string JSON) y marca el confirmado', () => {
		const wrapper = mount(RolesAplicados, { propsData: { rowData: fila() }, mocks });
		const chips = wrapper.findAll('.rol-chip');
		expect(chips.length).toBe(2);
		expect(chips.wrappers[0].classes()).toContain('label-success');
		expect(chips.wrappers[1].classes()).toContain('label-default');
	});

	it('acepta el formato legacy {id, text} y el array ya parseado', () => {
		const wrapper = mount(RolesAplicados, {
			propsData: { rowData: fila({ roles_aplicados: [{ id: 'camioneta', text: 'Camioneta' }], nombreRol: null }) }, mocks,
		});
		expect(wrapper.text()).toContain('Camioneta');
	});

	it('se actualiza cuando cambia rowData (vuetable reutiliza la celda)', () => {
		const wrapper = mount(RolesAplicados, { propsData: { rowData: fila() }, mocks });
		wrapper.setProps({ rowData: fila({ roles_aplicados: '["comunicacion"]', nombreRol: null }) });
		expect(wrapper.findAll('.rol-chip').length).toBe(1);
	});

	it('click en un rol no confirmado lo confirma y actualiza nombreRol', (done) => {
		const rowData = fila();
		const wrapper = mount(RolesAplicados, { propsData: { rowData }, mocks });
		moxios.stubRequest('/admin/ajax/actividades/99/inscripciones/asignar/rol', { status: 200, response: 'ok' });

		wrapper.findAll('.rol-chip').wrappers[1].trigger('click');

		moxios.wait(() => {
			const req = JSON.parse(moxios.requests.mostRecent().config.data);
			expect(req).toEqual({ rol: 'intendencia', inscripciones: [10] });
			expect(rowData.nombreRol).toBe('intendencia');
			done();
		});
	});
});

// Sugerir el dato estándar cuando una pregunta adicional lo duplica.
describe('PreguntasManager — sugerencia de dato estándar', () => {
	// Se prueba el método puro (sin montar: el componente carga las preguntas por axios).
	const montar = (props) => ({
		vm: { sugerenciaPara: (t) => PreguntasManager.methods.sugerenciaPara.call(Object.assign({ sugerirEstandar: false }, props), t) },
	});

	it('detecta rol, estudios y salud (con y sin tildes, también en portugués)', () => {
		const vm = montar({ sugerirEstandar: true }).vm;
		expect(vm.sugerenciaPara('¿A qué rol te gustaría postularte?')).toBe('roles');
		expect(vm.sugerenciaPara('Qual o seu cargo na CC?')).toBe('roles');
		expect(vm.sugerenciaPara('Seleccioná tu Universidad')).toBe('estudios');
		expect(vm.sugerenciaPara('¿Está tomando algún medicamento?')).toBe('salud');
		expect(vm.sugerenciaPara('Contacto de emergencia')).toBe('salud');
		expect(vm.sugerenciaPara('Qual seu contato para emergências?')).toBe('salud');
	});

	it('no sugiere en preguntas que no duplican un estándar', () => {
		const vm = montar({ sugerirEstandar: true }).vm;
		expect(vm.sugerenciaPara('Talla de camisa')).toBe(null);
		expect(vm.sugerenciaPara('¿Cómo controlás tu tiempo?')).toBe(null); // "control" no es "rol"
		// "vivienda/construcción de emergencia" es vocabulario de TECHO, no salud.
		expect(vm.sugerenciaPara('¿En qué construcción(es) de emergencia has participado?')).toBe(null);
	});

	it('en campañas (sin sugerir-estandar) no sugiere nada', () => {
		const vm = montar({}).vm;
		expect(vm.sugerenciaPara('¿Qué rol querés?')).toBe(null);
	});
});
