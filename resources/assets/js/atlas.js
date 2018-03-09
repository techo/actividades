Vue.config.devtools = true;
import Vue from 'vue';
import Login from './components/login.vue';
import Filtro from './components/filtro.vue';
import ContenedorDeTarjetas from './components/contenedorDeTarjetas';

require('./bootstrap');

window.Vue = require('vue');
window.events = new Vue();

Vue.component('filtro', Filtro);
Vue.component('login', Login);
Vue.component('contenedor-de-tarjetas', ContenedorDeTarjetas);
Vue.component('inscripciones', Inscripciones);

var app = new Vue({
    el: "#app",
    data() {
      return {
          meta: [],
      }
    }
});


