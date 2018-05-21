Vue.config.devtools = true;
import Vue from 'vue';
import VueRouter from 'vue-router';
import Login from './components/login.vue';
import Filtro from './components/filtro.vue';
import ContenedorDeTarjetas from './components/contenedorDeTarjetas';
import Inscripcion from './components/inscripcion';
import Autenticar from './components/autenticar';
import contenedorCheckProvincias from './components/contenedorCheckProvincias';
import Registro from './components/registro';
import Datepicker from 'vue2-datepicker';
import Perfil from './components/perfil/perfil';
import MisActividades from './components/perfil/actividades';
import Simplert from 'vue2-simplert';
import BootstrapVue from 'bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'


require('./bootstrap');

window.Vue = require('vue');
window.events = new Vue();

Vue.use(BootstrapVue);

Vue.component('filtro', Filtro);
Vue.component('login', Login);
Vue.component('autenticar', Autenticar);
Vue.component('contenedor-de-tarjetas', ContenedorDeTarjetas);
Vue.component('inscripcion', Inscripcion);
Vue.component('contenedor-check-provincias', contenedorCheckProvincias);
Vue.component('registro', Registro);
Vue.component('perfil', Perfil);
Vue.component('mis-inscripciones', MisActividades);
Vue.component('datepicker', Datepicker);
Vue.component('simplert', Simplert);



var app = new Vue({
    el: "#app",
    data() {
      return {
          meta: [],
      }
    }
});


