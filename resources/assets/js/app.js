Vue.config.devtools = true;
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'
import ActividadesShow from './components/backoffice/actividades/actividades-show'
//import vSelect from './components/backoffice/select2/Select2'


// Vue.component('v-select', vSelect);
Vue.component('datatable', VueTable);
Vue.component('actividades-show', ActividadesShow);

const app = new Vue({
    el: '#app'
});
