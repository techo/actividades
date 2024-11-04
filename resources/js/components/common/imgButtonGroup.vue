<template>
  <div class="container">
    <div class="position-relative w-100 mb-4">
      <button v-if="showArrows" ref="flechaIzquierda" role="button" class="flecha-izquierda" @click="scrollLeft">
        <
      </button>
        
      <div class="scroll-container d-flex flex-nowrap overflow-auto">
        <div class="flex-shrink-0" v-for="(button, index) in buttons" :key="index">
          <imgButton :url="button.url" :img="button.img" :text="button.text" />
        </div>
      </div>
      
      <button v-if="showArrows" ref="flechaDerecha" role="button" class="flecha-derecha"
        @click="scrollRight">
            >
        </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ButtonGroup',
  data() {
    return {
      buttons: [
        {
          url: '?tipo=27',
          img: '/img/tipoActividad/construcciones.png',
          text: 'Construcciones',
        },
        {
          url: 'https://ejemplo.com/mesas',
          img: '/img/tipoActividad/mesaTrabajo.png',
          text: 'Mesas de Trabajo',
        },
        {
          url: 'https://ejemplo.com/infraestructura',
          img: '/img/tipoActividad/infraestructura.png',
          text: 'Infraestructura',
        },
        {
          url: 'https://ejemplo.com/espacios',
          img: '/img/tipoActividad/formacion.png',
          text: 'Espacios Formativos',
        },
        {
          url: 'https://ejemplo.com/encuentros',
          img: '/img/tipoActividad/encuentros.png',
          text: 'Encuentros',
        },
        {
          url: 'https://ejemplo.com/colecta',
          img: '/img/tipoActividad/colecta.png',
          text: 'Colecta',
        },
        {
          url: 'https://ejemplo.com/eventos',
          img: '/img/tipoActividad/eventos.png',
          text: 'Eventos y Otros',
        },
      ],
      showArrows: false,
    };
  },
  mounted() {
    this.checkScrollArrows();
    window.addEventListener('resize', this.checkScrollArrows);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.checkScrollArrows);
  },
  methods: {
    scrollLeft() {
      const container = this.$el.querySelector('.scroll-container');
      container.scrollLeft -= 100;
    },
    scrollRight() {
      const container = this.$el.querySelector('.scroll-container');
      container.scrollLeft += 100;
    },
    checkScrollArrows() {
      const container = this.$el.querySelector('.scroll-container');
      this.showArrows = container.scrollWidth > container.clientWidth;
    },
  },
};
</script>

<style scoped>
.scroll-container {
  overflow-x: auto;
  scroll-behavior: smooth;
  -ms-overflow-style: none;  /* Ocultar barra en Internet Explorer y Edge */
  scrollbar-width: none;      /* Ocultar barra en Firefox */
}

.scroll-container::-webkit-scrollbar {
  display: none;              /* Ocultar barra en Chrome, Safari y Opera */
}

button {
  z-index: 1;
  height: 50px; /* Ajustar la altura de los botones */
  width: 50px; /* Ajustar el ancho de los botones */
}

.flecha-izquierda,
.flecha-derecha {
    position: absolute;
    border: none;
    background: rgba(0, 0, 0, 0);
    font-size: 45px;
    height: 50%;
    top: calc(50% - 25%);
    line-height: 40px;
    width: 50px;
    color: rgb(78, 76, 76);
    cursor: pointer;
    z-index: 500;
    transition: 0.2s ease all;
    outline: none;
}

.flecha-izquierda {
    left: -26px;
}

.flecha-derecha {
    right: -26px;
}
</style>
