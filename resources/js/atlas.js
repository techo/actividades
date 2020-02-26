
import Vue from 'vue';

import VueInternationalization from 'vue-i18n';
import Locale from './vue-i18n-locales.generated';
Vue.use(VueInternationalization);
const lang = document.documentElement.lang;
const i18n = new VueInternationalization({
    locale: lang,
    messages: Locale
});


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
import EvaluacionPersonal from './components/perfil/evaluacion';
import ContenedorEvaluaciones from './components/evaluaciones/contenedorEvaluaciones';
import DataTable from './components/datatable/DataTable';
import BtnMisActividades from './components/perfil/btnMisActividades';
import TarjetaHorizontal from './components/perfil/tarjeta-horizontal';
import CookiesBar from './components/cookies-bar';
import EstadisticasPublicas from './components/estadisticas-publicas';


import axios from 'axios';

window.Vue = require('vue');
window.moment = require('moment');
window.events = new Vue();
window.axios = axios;

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

Vue.use(BootstrapVue);

Vue.component('filtro', Filtro);
Vue.component('login', Login);
Vue.component('autenticar', Autenticar);
Vue.component('contenedor-de-tarjetas', ContenedorDeTarjetas);
Vue.component('inscripcion', Inscripcion);
Vue.component('contenedor-check-provincias', contenedorCheckProvincias);
Vue.component('registro', Registro);
Vue.component('perfil', Perfil);
Vue.component('mis-actividades', MisActividades);
Vue.component('datepicker', Datepicker);
Vue.component('simplert', Simplert);
Vue.component('evaluar-actividad', EvaluarActividad);
Vue.component('evaluacion-personal', EvaluacionPersonal);
Vue.component('contenedor-evaluaciones', ContenedorEvaluaciones);
Vue.component('datatable', DataTable);
Vue.component('btn-mis-actividades', BtnMisActividades);
Vue.component('tarjeta-horizontal', TarjetaHorizontal);
Vue.component('cookies-bar', CookiesBar);
Vue.component('estadisticas-publicas', EstadisticasPublicas);

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
    i18n,
    data() {
      return {
          meta: [],
      }
    }
});


