import Simplert from "vue2-simplert";

Vue.config.devtools = true;
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'
import InscripcionesTable from './components/backoffice/datatable/InscripcionesTable'
import ActividadesShow from './components/backoffice/actividades/actividades-show'
import FiltrosInscripciones from './components/backoffice/actividades/filtros-inscripciones'
import CondicionesSeleccionadas from './components/backoffice/actividades/condiciones-seleccionadas'
import InscripcionesMensajes from './components/backoffice/actividades/inscripciones-mensajes'
import InscripcionesGrupoModal from './components/backoffice/actividades/inscripciones-grupo-modal'
import InscripcionesRolModal from './components/backoffice/actividades/inscripciones-rol-modal'
import InscripcionesPuntoModal from './components/backoffice/actividades/inscripciones-punto-modal'
import InscripcionesImportarModal from './components/backoffice/actividades/inscripciones-importar-modal'
import asignacionDeRol from './components/backoffice/roles/asignacionDeRol'
import CrudFooter from './components/backoffice/crudFooter';
import BtnGrupoPersona from './components/backoffice/grupos/btnGrupoPersona';
import Datepicker from 'vuejs-datepicker'; // https://github.com/charliekassel/vuejs-datepicker
import vSelect2 from 'vue-select';
import vSwitch from 'vue-switches';
import axios from 'axios';
import Miembros from './components/backoffice/grupos/Miembros';
import MiembrosTabla from './components/backoffice/grupos/MiembrosTabla'

Vue.component('actividades-show', ActividadesShow);
Vue.component('filtros-inscripciones', FiltrosInscripciones);
Vue.component('condiciones-seleccionadas', CondicionesSeleccionadas);
Vue.component('inscripciones-mensajes', InscripcionesMensajes);
Vue.component('inscripciones-rol-modal', InscripcionesRolModal);
Vue.component('inscripciones-grupo-modal', InscripcionesGrupoModal);
Vue.component('inscripciones-punto-modal', InscripcionesPuntoModal);
Vue.component('inscripciones-importar-modal', InscripcionesImportarModal);
Vue.component('asignacion-de-rol', asignacionDeRol);
Vue.component('crud-footer', CrudFooter);
Vue.component('datatable', VueTable);
Vue.component('inscripciones-table', InscripcionesTable);
Vue.component('datepicker', Datepicker);
Vue.component('v-select', vSelect2);
Vue.component('simplert', Simplert);
Vue.component('v-switch', vSwitch);
Vue.component('btn-grupo-persona', BtnGrupoPersona);
Vue.component('miembros', Miembros);
Vue.component('miembros-tabla', MiembrosTabla)

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

const app = new Vue({
    el: '#app',
});