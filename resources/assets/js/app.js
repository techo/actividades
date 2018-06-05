import Simplert from "vue2-simplert";

Vue.config.devtools = true;
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'
import ActividadesShow from './components/backoffice/actividades/actividades-show'
import FiltrosInscripciones from './components/backoffice/actividades/filtros-inscripciones'
import InscripcionesToolbar from './components/backoffice/actividades/inscripciones-toolbar'
import CondicionesSeleccionadas from './components/backoffice/actividades/condiciones-seleccionadas'
import asignacionDeRol from './components/backoffice/roles/asignacionDeRol'
import CrudFooter from './components/backoffice/crudFooter'
import Datepicker from 'vuejs-datepicker'; // https://github.com/charliekassel/vuejs-datepicker
import vSelect2 from 'vue-select';
import vSwitch from 'vue-switches';

Vue.component('actividades-show', ActividadesShow);
Vue.component('filtros-inscripciones', FiltrosInscripciones);
Vue.component('inscripciones-toolbar', InscripcionesToolbar);
Vue.component('condiciones-seleccionadas', CondicionesSeleccionadas);
Vue.component('asignacion-de-rol', asignacionDeRol);
Vue.component('crud-footer', CrudFooter);
Vue.component('datatable', VueTable);
Vue.component('datepicker', Datepicker);
Vue.component('v-select', vSelect2);
Vue.component('simplert', Simplert);
Vue.component('v-switch', vSwitch);


window.Event = new Vue();

const app = new Vue({
    el: '#app'
});
