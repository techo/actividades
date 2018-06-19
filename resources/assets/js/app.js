import Simplert from "vue2-simplert";

Vue.config.devtools = true;
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'
import InscripcionesTable from './components/backoffice/datatable/InscripcionesTable'
import ActividadesShow from './components/backoffice/actividades/actividades-show'
import FiltrosInscripciones from './components/backoffice/actividades/filtros-inscripciones'
import CondicionesSeleccionadas from './components/backoffice/actividades/condiciones-seleccionadas'
import InscripcionesMensajes from './components/backoffice/actividades/inscripciones-mensajes'
import InscripcionesGrupoModal from './components/backoffice/actividades/inscripciones-grupo-modal'
import InscripcionesRolModal from './components/backoffice/actividades/inscripciones-rol-modal'
import InscripcionesPuntoModal from './components/backoffice/actividades/inscripciones-punto-modal'
import asignacionDeRol from './components/backoffice/roles/asignacionDeRol'
import CrudFooter from './components/backoffice/crudFooter';
import BtnGrupoPersona from './components/backoffice/grupos/btnGrupoPersona';
import Datepicker from 'vuejs-datepicker'; // https://github.com/charliekassel/vuejs-datepicker
import vSelect2 from 'vue-select';
import vSwitch from 'vue-switches';
import Miembros from './components/backoffice/grupos/Miembros';
import MiembrosTabla from './components/backoffice/grupos/MiembrosTabla'

Vue.component('actividades-show', ActividadesShow);
Vue.component('filtros-inscripciones', FiltrosInscripciones);
Vue.component('condiciones-seleccionadas', CondicionesSeleccionadas);
Vue.component('inscripciones-mensajes', InscripcionesMensajes);
Vue.component('inscripciones-rol-modal', InscripcionesRolModal);
Vue.component('inscripciones-grupo-modal', InscripcionesGrupoModal);
Vue.component('inscripciones-punto-modal', InscripcionesPuntoModal);
Vue.component('asignacion-de-rol', asignacionDeRol);
Vue.component('crud-footer', CrudFooter);
Vue.component('datatable', VueTable);
Vue.component('inscripciones-table', InscripcionesTable);
Vue.component('datepicker', Datepicker);
Vue.component('v-select', vSelect2);
Vue.component('simplert', Simplert);
Vue.component('v-switch', vSwitch);
Vue.component('btn-grupo-persona', BtnGrupoPersona);
Vue.component('miembros', Miembros);
Vue.component('miembros-tabla', MiembrosTabla)

window.Event = new Vue();

const app = new Vue({
    el: '#app'
});