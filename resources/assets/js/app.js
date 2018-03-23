Vue.config.devtools = true;
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'

Vue.component('datatable', VueTable);

const app = new Vue({
    el: '#app'
});
