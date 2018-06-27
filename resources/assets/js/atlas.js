Vue.config.devtools = true;
import Vue from 'vue';
// import VueRouter from 'vue-router';
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
import EvaluarActividad from './components/evaluaciones/evaluarActividad';
import ContenedorEvaluaciones from './components/evaluaciones/contenedorEvaluaciones';

import axios from 'axios';

window.Vue = require('vue');
window.events = new Vue();
window.axios = axios;

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
Vue.component('evaluar-actividad', EvaluarActividad);
Vue.component('contenedor-evaluaciones', ContenedorEvaluaciones);

window.Event = new Vue();

Vue.mixin({
    methods: {
        axiosPost(url, fCallback, params = [], fError = function(){}) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            axios.post(url, params)
                .then(response => {
                    fCallback(response.data, this);
                    Event.$emit('success');
                    this.readonly = true;
                })
                .catch((error) => {
                    fError(error, this); //handler personalizado
                    Event.$emit('error');
                    // Error
                    console.info('Error en: ' + url);
                    console.error(error.response.status);
                    if (error.request) {
                        console.error(error.request);
                    } else {
                        console.error('Error', error.message);
                    }
                    console.error(error.config);
                });
        },
        axiosGet(url, fCallback, params = [], fError = function(){}) {
            axios.get(url, params)
                .then(response => {
                    fCallback(response.data, this)
                })
                .catch((error) => {
                    // Error
                    fError(error, this); //handler personalizado
                    console.error('Error en: ' + url);
                    if (error.response) {
                        console.error(error.response.data);
                        console.error(error.response.status);
                        console.error(error.response.headers);
                    } else if (error.request) {
                        console.error(error.request);
                    } else {
                        console.error('Error', error.message);
                    }
                    console.error(error.config);
                });

        },
    }
});


var app = new Vue({
    el: "#app",
    data() {
      return {
          meta: [],
      }
    }
});


