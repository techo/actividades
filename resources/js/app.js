require('./bootstrap.js');

import VueInternationalization from 'vue-i18n';
import Locale from './vue-i18n-locales.generated';
Vue.use(VueInternationalization);
const lang = document.documentElement.lang;
const i18n = new VueInternationalization({
    locale: lang,
    messages: Locale
});

import Simplert from "vue2-simplert";
import Vue from 'vue';
import VueTable from './components/backoffice/datatable/MyVuetable'
import InscripcionesTable from './components/backoffice/datatable/InscripcionesTable'
import Actividad from './components/backoffice/actividades/actividad'
import Puntos from './components/backoffice/actividades/puntos'
import Accesos from './components/backoffice/actividades/accesos'
import ModalAuditoria from './components/backoffice/auditorias/ModalAuditoria';
import FiltrosInscripciones from './components/backoffice/actividades/filtros-inscripciones'
import CondicionesSeleccionadas from './components/backoffice/actividades/condiciones-seleccionadas'
import InscripcionesMensajes from './components/backoffice/actividades/inscripciones-mensajes'
import InscripcionesGrupoModal from './components/backoffice/actividades/inscripciones-grupo-modal'
import InscripcionesRolModal from './components/backoffice/actividades/inscripciones-rol-modal'
import InscripcionesPuntoModal from './components/backoffice/actividades/inscripciones-punto-modal'
import InscripcionesDesinscribirModal from './components/backoffice/actividades/inscripciones-desinscribir-modal'
import InscripcionesImportarModal from './components/backoffice/actividades/inscripciones-importar-modal'
import asignacionDeRol from './components/backoffice/roles/asignacionDeRol'
import CrudFooter from './components/backoffice/crudFooter';
import BtnGrupoPersona from './components/backoffice/grupos/btnGrupoPersona';
import Datepicker from 'vuejs-datepicker'; // https://github.com/charliekassel/vuejs-datepicker
import vSelect2 from 'vue-select';
import 'vue-select/dist/vue-select.css';
import vSwitch from 'vue-switches';
import axios from 'axios';
import Miembros from './components/backoffice/grupos/Miembros';
import MiembrosTabla from './components/backoffice/grupos/MiembrosTabla';
import BtnEnviarEvaluaciones from './components/backoffice/evaluaciones/btnEnviarEvaluaciones';
import store from './components/backoffice/stores/store';
import EvaluacionesActividad from './components/backoffice/evaluaciones/evaluacionesActividad';
import EvaluacionesVoluntarios from './components/backoffice/evaluaciones/evaluacionesVoluntarios';
import EvaluacionesGeneralStats from './components/backoffice/evaluaciones/evaluacionesGeneralStats';
import EvaluacionesActividadStats from './components/backoffice/evaluaciones/EvaluacionesActividadStats';
import EvaluacionesVoluntariosStats from './components/backoffice/evaluaciones/EvaluacionesVoluntariosStats';
import EvaluacionesActividadChart from './components/backoffice/evaluaciones/EvaluacionesActividadChart';
import EvaluacionesVoluntariosChart from './components/backoffice/evaluaciones/EvaluacionesVoluntariosChart';
import UsuariosDatatable from './components/backoffice/datatable/UsuariosDatatable';
import SuscriptosDatatable from './components/backoffice/datatable/SuscriptosDatatable';
import EquiposDatatable from './components/backoffice/datatable/EquiposDatatable';
import IntegrantesDatatable from './components/backoffice/datatable/IntegrantesDatatable';
import IntegranteModal from './components/backoffice/equipos/integrante-modal';
import UsuariosFilterBar from './components/backoffice/datatable/UsuariosFilterBar';
import UsuariosForm from './components/backoffice/usuarios/usuario-form';
import EquiposForm from './components/backoffice/equipos/equipos-form';
import CoordinadoresEquipo from './components/backoffice/equipos/coordinadores-equipo';
import HomeHeaderForm from './components/backoffice/homeHeader/home-header-form';
import UsuariosFichaTab from './components/backoffice/usuarios/usuario-ficha-tab';
import UsuariosEstudiosTab from './components/backoffice/usuarios/usuarios-estudios-tab';

import TiposActividadDatatable from './components/backoffice/datatable/TiposActividadDatatable';
import TiposActividadFilterBar from './components/backoffice/datatable/TiposActividadFilterBar';
import TiposActividadForm from './components/backoffice/tiposActividad/tipos-actividad-form';


import VueTelInput from 'vue-tel-input';
import 'vue-tel-input/dist/vue-tel-input.css';

Vue.component('vue-tel-input', VueTelInput);

Vue.component('tipos-actividad-datatable', TiposActividadDatatable);
Vue.component('tipos-actividad-filter-bar', TiposActividadFilterBar);
Vue.component('tipos-actividad-form', TiposActividadForm);
Vue.component('coordinadores-equipo', CoordinadoresEquipo);

import OficinasDatatable from './components/backoffice/datatable/OficinasDatatable';
import OficinasFilterBar from './components/backoffice/datatable/OficinasFilterBar';
import OficinasForm from './components/backoffice/oficinas/oficina-form';

import ProvinciasDatatable from './components/backoffice/datatable/configuracion/ProvinciasDatatable';
import ProvinciaForm from './components/backoffice/configuracion/provincias/provincia-form';



import LocalidadModal from './components/backoffice/configuracion/provincias/localidad-modal';
import LocalidadesDatatable from './components/backoffice/datatable/configuracion/provincias/LocalidadesDatatable';

Vue.component('provincias-datatable', ProvinciasDatatable);
Vue.component('provincia-form', ProvinciaForm);
Vue.component('localidad-modal', LocalidadModal);
Vue.component('localidades-datatable', LocalidadesDatatable);

import InstitucionEducativaDatatable from './components/backoffice/datatable/configuracion/InstitucionEducativaDatatable';
import InstitucionEducativaForm from './components/backoffice/configuracion/institucion-educativa-form';

Vue.component('institucion-educativa-datatable', InstitucionEducativaDatatable);
Vue.component('institucion-educativa-form', InstitucionEducativaForm);

import UsuariosInscripcionesTab from './components/backoffice/usuarios/usuarios-inscripciones-tab';
Vue.component('usuarios-inscripciones-tab', UsuariosInscripcionesTab);

import UsuariosEvaluacionesTab from './components/backoffice/usuarios/usuarios-evaluaciones-tab';
Vue.component('usuarios-evaluaciones-tab', UsuariosEvaluacionesTab);

import EstadisticasGenerales from './components/backoffice/estadisticas/estadisticas-generales';
Vue.component('estadisticas-generales', EstadisticasGenerales);

import EstadisticasActividades from './components/backoffice/estadisticas/estadisticas-actividades';
Vue.component('estadisticas-actividades', EstadisticasActividades);

import EstadisticasPersonas from './components/backoffice/estadisticas/estadisticas-personas';
Vue.component('estadisticas-personas', EstadisticasPersonas);

import EstadisticasCoordinadores from './components/backoffice/estadisticas/estadisticas-coordinadores';
Vue.component('estadisticas-coordinadores', EstadisticasCoordinadores);

import Novedades from './components/backoffice/novedades';
Vue.component('novedades', Novedades);

import GrupoEditModal from './components/backoffice/grupos/grupo-edit-modal'
Vue.component('grupo-edit-modal', GrupoEditModal);


Vue.component('actividad', Actividad);
Vue.component('puntos', Puntos);
Vue.component('accesos', Accesos);
Vue.component('modal-auditoria', ModalAuditoria);
Vue.component('filtros-inscripciones', FiltrosInscripciones);
Vue.component('condiciones-seleccionadas', CondicionesSeleccionadas);
Vue.component('inscripciones-mensajes', InscripcionesMensajes);
Vue.component('inscripciones-rol-modal', InscripcionesRolModal);
Vue.component('inscripciones-grupo-modal', InscripcionesGrupoModal);
Vue.component('inscripciones-punto-modal', InscripcionesPuntoModal);
Vue.component('inscripciones-desinscribir-modal', InscripcionesDesinscribirModal);
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
Vue.component('miembros-tabla', MiembrosTabla);
Vue.component('btn-enviar-evaluaciones', BtnEnviarEvaluaciones);
Vue.component('evaluaciones-actividad', EvaluacionesActividad);
Vue.component('evaluaciones-voluntarios', EvaluacionesVoluntarios);
Vue.component('evaluaciones-general-stats', EvaluacionesGeneralStats);
Vue.component('evaluaciones-actividad-stats', EvaluacionesActividadStats);
Vue.component('evaluaciones-voluntarios-stats', EvaluacionesVoluntariosStats);
Vue.component('evaluaciones-actividad-chart', EvaluacionesActividadChart);
Vue.component('evaluaciones-voluntarios-chart', EvaluacionesVoluntariosChart);
Vue.component('usuarios-datatable', UsuariosDatatable);
Vue.component('suscriptos-datatable', SuscriptosDatatable);
Vue.component('equipos-datatable', EquiposDatatable);
Vue.component('integrantes-datatable', IntegrantesDatatable);
Vue.component('integrante-modal', IntegranteModal);
Vue.component('usuarios-filter-bar', UsuariosFilterBar);
Vue.component('usuario-form', UsuariosForm);
Vue.component('equipo-form', EquiposForm);
Vue.component('home-header-form', HomeHeaderForm);
Vue.component('usuario-ficha-tab', UsuariosFichaTab);
Vue.component('usuarios-estudios-tab', UsuariosEstudiosTab);
Vue.component('oficinas-datatable', OficinasDatatable);
Vue.component('oficinas-filter-bar', OficinasFilterBar);
Vue.component('oficina-form', OficinasForm);




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
    i18n,
    store,
});
