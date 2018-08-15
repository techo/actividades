import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        idActividad: null,
        presentes: 0,
        inscriptos: 0,
        esConstruccion: false
    },
    mutations: {
        initIdActividad(state, id) {
            state.idActividad = id;
        },
        updatePresentes(state, valor) {
            state.presentes = valor;
        },
        updateInscriptos(state, valor){
            state.inscriptos = valor;
        },
        updateEsConstruccion(state, valor){
            state.esConstruccion = valor;
        }
    },
    actions: {
        //
    }
})