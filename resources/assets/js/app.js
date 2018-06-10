import Simplert from "vue2-simplert";

Vue.config.devtools = true;
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'
import ActividadesShow from './components/backoffice/actividades/actividades-show'
import asignacionDeRol from './components/backoffice/roles/asignacionDeRol'
import CrudFooter from './components/backoffice/crudFooter';
import BtnGrupoPersona from './components/backoffice/grupos/btnGrupoPersona';
import Datepicker from 'vuejs-datepicker'; // https://github.com/charliekassel/vuejs-datepicker
import vSelect2 from 'vue-select';
import vSwitch from 'vue-switches';
import Miembros from './components/backoffice/grupos/Miembros';
import MiembrosTabla from './components/backoffice/grupos/MiembrosTabla'

Vue.component('actividades-show', ActividadesShow);
Vue.component('asignacion-de-rol', asignacionDeRol);
Vue.component('crud-footer', CrudFooter);
Vue.component('datatable', VueTable);
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