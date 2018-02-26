Vue.config.devtools = true;
import Vue from 'vue';
import Tarjeta from './components/tarjeta.vue';
import Paginado from './components/paginado.vue';

Vue.component('tarjeta', Tarjeta);
Vue.component('paginado', Paginado);

new Vue({
    el: "#app",
    data() {
        return {
            actividades: [],
            meta: []
        }
    },
    mounted: function () {
        var self = this;
        $.ajax({
            url: '/ajax/actividades',
            method: 'GET',
            success: function (data) {
                console.log(data.data);
                self.actividades = data.data;
                self.meta = data
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

});
