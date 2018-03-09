Vue.config.devtools = true;
import Vue from 'vue';
import axios from 'axios';
import Login from './components/login.vue';
import Tarjeta from './components/tarjeta.vue';
import Filtro from './components/filtro.vue';

Vue.component('tarjeta', Tarjeta);
Vue.component('filtro', Filtro);
Vue.component('login', Login);

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
          const scrollY = window.scrollY;
          const visible = document.documentElement.clientHeight
          const pageHeight = document.documentElement.scrollHeight
          const bottomOfPage = visible + scrollY >= pageHeight
          return bottomOfPage || pageHeight < visible
      },
      agregarTarjetas() {
          let self = this;
          self.page++;
          self.loading = true;
          axios.get('/ajax/actividades?page='+self.page)
              .then(response => {
                  self.meta = response
                  for(let i in response.data.data) {
                      let item = response.data.data[i]
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


