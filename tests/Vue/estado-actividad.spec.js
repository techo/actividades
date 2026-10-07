import { mount } from 'vue-test-utils';
import expect from 'expect';
import moment from 'moment';

import EstadoActividad from '../../resources/js/components/backoffice/datatable/estadoActividad.vue';

global.moment = window.moment = moment;

// Task 49 / reclamo #8: vuetable-2 reutiliza el componente de la celda por posición, así que
// al paginar cambia rowData sin volver a montar. La etiqueta tiene que seguir a rowData.
describe('estadoActividad — etiqueta de inscripciones', () => {

	const fmt = d => d.format('YYYY-MM-DD HH:mm:ss');
	const abierta = {
		estadoConstruccion: 'Abierta',
		fechaInicioInscripciones: fmt(moment().subtract(1, 'days')),
		fechaFinInscripciones: fmt(moment().add(1, 'days')),
	};
	const cerrada = Object.assign({}, abierta, { fechaFinInscripciones: fmt(moment().subtract(1, 'hours')) });

	const montar = rowData => mount(EstadoActividad, {
		propsData: { rowData },
		mocks: { $t: key => key },
	});

	it('se actualiza cuando cambia rowData (sin re-montar)', () => {
		const wrapper = montar(cerrada);
		expect(wrapper.vm.estadoInscripcion).toBe('cerradas');

		wrapper.setProps({ rowData: abierta });
		expect(wrapper.vm.estadoInscripcion).toBe('abiertas');
		expect(wrapper.text()).toContain('backend.open_registrations');
	});

	it('fechas NULL no restringen (igual que el server)', () => {
		const wrapper = montar({ estadoConstruccion: 'Abierta', fechaInicioInscripciones: null, fechaFinInscripciones: null });
		expect(wrapper.vm.estadoInscripcion).toBe('abiertas');
	});

	it('distingue "aún no abren" de "cerradas"', () => {
		const wrapper = montar(Object.assign({}, abierta, { fechaInicioInscripciones: fmt(moment().add(2, 'days')) }));
		expect(wrapper.vm.estadoInscripcion).toBe('proximas');
	});

	it('actividad no Abierta siempre es cerradas', () => {
		const wrapper = montar(Object.assign({}, abierta, { estadoConstruccion: 'Cerrada' }));
		expect(wrapper.vm.estadoInscripcion).toBe('cerradas');
	});
});
