import { shallow } from 'vue-test-utils';
import expect from 'expect';
import Index from '../../resources/js/components/index.vue';

/**
 * Regresión del bug del home: el componente <index> agrupaba actividades por
 * categoría pero solo consideraba las categorías que le pasaban. El Blade le
 * pasaba `[1,2,3,5]` hardcodeado y el template solo renderizaba los buckets
 * 1, 2 y 5. Ahora recibe TODAS las categorías (JSON) y renderiza un carrusel
 * por cada una, así que las actividades de categorías fuera de ese set legacy
 * dejan de quedar invisibles.
 */
describe('Home: actividades por categoría', () => {
    const mocks = { $t: key => key };

    it('inicializa un bucket por cada categoría del prop, no solo el set legacy [1,2,5]', () => {
        const categorias = JSON.stringify([
            { id: 1, nombre: 'Comunidad' },
            { id: 2, nombre: 'Formación' },
            { id: 999, nombre: 'Categoría fuera del set legacy' },
        ]);

        const wrapper = shallow(Index, { propsData: { categorias }, mocks });

        // Se crea un bucket por cada categoría recibida (incluida la 999).
        expect(Object.keys(wrapper.vm.actividadesPorCategoria).sort()).toEqual(['1', '2', '999']);
    });

    it('parsea el prop JSON a la lista de categorías que recorre el home', () => {
        const categorias = JSON.stringify([
            { id: 1, nombre: 'Comunidad' },
            { id: 999, nombre: 'Categoría fuera del set legacy' },
        ]);

        const wrapper = shallow(Index, { propsData: { categorias }, mocks });

        expect(wrapper.vm.listaCategorias.length).toBe(2);
        expect(wrapper.vm.listaCategorias.map(c => c.id)).toEqual([1, 999]);
    });

    it('tituloCategoria usa i18n para las categorías conocidas y el nombre de la DB para el resto', () => {
        const wrapper = shallow(Index, { propsData: { categorias: '[]' }, mocks });

        expect(wrapper.vm.tituloCategoria({ id: 1, nombre: 'X' })).toBe('frontend.home_community');
        expect(wrapper.vm.tituloCategoria({ id: 2, nombre: 'X' })).toBe('frontend.home_formation');
        expect(wrapper.vm.tituloCategoria({ id: 5, nombre: 'X' })).toBe('frontend.home_campaign');
        expect(wrapper.vm.tituloCategoria({ id: 999, nombre: 'Especial' })).toBe('Especial');
    });
});
