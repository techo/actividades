import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

export default new Vuex.Store({
    state: {
        idActividad: null
    },
    mutations: {
        initIdActividad(state, id) {
            state.idActividad = id;
        },
    },
    actions: {
        //
    }
})