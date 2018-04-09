Vue.config.devtools = true;
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'
import ActividadesShow from './components/backoffice/actividades/actividades-show'
import CrudFooter from './components/backoffice/crudFooter'
import Datepicker from 'vuejs-datepicker';
import vSelect2 from 'vue-select';
import vSwitch from 'vue-switches';

Vue.component('actividades-show', ActividadesShow);
Vue.component('crud-footer', CrudFooter);
Vue.component('datatable', VueTable);
Vue.component('datepicker', Datepicker);
Vue.component('v-select', vSelect2);
Vue.component('v-switch', vSwitch);

const app = new Vue({
    el: '#app'
});
