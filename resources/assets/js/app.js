Vue.config.devtools = true;
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'
import ActividadesShow from './components/backoffice/actividades/actividades-show'
import CrudFooter from './components/backoffice/crudFooter'
import Datepicker from 'vuejs-datepicker'; //https://github.com/charliekassel/vuejs-datepicker
import vSelect2 from 'vue-select';  // https://sagalbot.github.io/vue-select/docs/
import vSwitch from 'vue-switches'; // https://github.com/drewjbartlett/vue-switches

Vue.component('actividades-show', ActividadesShow);
Vue.component('crud-footer', CrudFooter);
Vue.component('datatable', VueTable);
Vue.component('datepicker', Datepicker);
Vue.component('v-select', vSelect2);
Vue.component('v-switch', vSwitch);

window.Event = new Vue();

const app = new Vue({
    el: '#app'
});
