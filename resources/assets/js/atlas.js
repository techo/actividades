Vue.config.devtools = true;
import Vue from 'vue';
import axios from 'axios';
import Login from './components/login'
import Tarjeta from './components/tarjeta.vue';
import Inscripciones from './components/inscripciones.vue';

Vue.component('tarjeta', Tarjeta);
Vue.component('login', Login);
Vue.component('inscripciones', Inscripciones);

new Vue({
  el: "#app",
  data() {
      return {
          page: 0,
          bottom: false,
          actividades: [],
          meta: [],
          loading: false
      }
  },
  created() {
      window.addEventListener('scroll', () => {
          this.bottom = this.bottomVisible()
      })
      this.agregarTarjetas()
  },
  methods: {
      bottomVisible() {
          const scrollY = window.scrollY
          const visible = document.documentElement.clientHeight
          const pageHeight = document.documentElement.scrollHeight
          const bottomOfPage = visible + scrollY >= pageHeight
          return bottomOfPage || pageHeight < visible
      },
      agregarTarjetas() {
          var self = this;
          self.page++
          self.loading = true;
          axios.get('/ajax/actividades?page='+self.page)
              .then(response => {
                  self.meta = response
                  for(var i in response.data.data) {
                      var item = response.data.data[i]
                      self.actividades.push(item)
                  }
                  self.loading = false;
                  if (self.bottomVisible()) {
                      self.agregarTarjetas()
                  }
              })
      }
  },
  watch: {
    bottom(bottom) {
      if (bottom) {
        this.agregarTarjetas()
      }
    }
  },
});


