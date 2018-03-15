Vue.config.devtools = true;
import Vue from 'vue';
import Login from './components/login';
import Filtro from './components/filtro';
import ContenedorDeTarjetas from './components/contenedorDeTarjetas';
import Inscripciones from './components/inscripciones';
import Share from './components/share';
import ContenedorDeCheckboxList from './components/contenedorDeCheckboxList';

require('./bootstrap');

window.Vue = require('vue');
window.events = new Vue();

Vue.component('filtro', Filtro);
Vue.component('login', Login);
Vue.component('contenedor-de-tarjetas', ContenedorDeTarjetas);
Vue.component('inscripciones', Inscripciones);
Vue.component('share', Share);
Vue.component('contenedor-de-checkbox-list', ContenedorDeCheckboxList);

var app = new Vue({
    el: "#app",
    data() {
      return {
          meta: [],
      }
    }
});


