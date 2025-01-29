<template>
  <div class="container">
    <h4 class="text-center fw-bold pb-2">¡Sumate y <span class="techo-blue">transforma</span>!</h4>

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
          url: 'filtro?tipo[]=11&tipo[]=27&tipo[]=65&tipo[]=72&tipo[]=73&tipo[]=80&tipo[]=81&tipo[]=98&tipo[]=105&tipo[]=114&tipo[]=115',
          img: '/img/tipoActividad/construcciones.png',
          text: 'Construcciones',
        },
        {
          url: 'filtro?tipo[]=25&tipo[]=28&tipo[]=29&tipo[]=75&tipo[]=76&tipo[]=82&tipo[]=83&tipo[]=85&tipo[]=113&tipo[]=117',
          img: '/img/tipoActividad/mesaTrabajo.png',
          text: 'Mesas de Trabajo',
        },
        {
          url: 'filtro?tipo[]=22&tipo[]=32&tipo[]=77&tipo[]=79&tipo[]=97&tipo[]=103',
          img: '/img/tipoActividad/infraestructura.png',
          text: 'Infraestructura',
        },
        {
          url: 'filtro?tipo[]=23&tipo[]=30&tipo[]=31&tipo[]=33&tipo[]=34&tipo[]=35&tipo[]=36&tipo[]=45&tipo[]=46&tipo[]=47&tipo[]=49&tipo[]=52&tipo[]=53&tipo[]=56&tipo[]=58&tipo[]=59&tipo[]=62&tipo[]=89',
          img: '/img/tipoActividad/formacion.png',
          text: 'Espacios Formativos',
        },
        {
          url: 'filtro?tipo[]=54&tipo[]=55&tipo[]=63&tipo[]=64&tipo[]=68&tipo[]=69&tipo[]=71&tipo[]=86&tipo[]=88&tipo[]=90',
          img: '/img/tipoActividad/encuentros.png',
          text: 'Encuentros',
        },
        {
          url: 'filtro?tipo[]=43&tipo[]=96',
          img: '/img/tipoActividad/colecta.png',
          text: 'Colecta',
        },
        {
          url: 'filtro?tipo[]=44&tipo[]=48&tipo[]=60&tipo[]=101&tipo[]=104',
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
    left: -36px;
}

.flecha-derecha {
    right: -36px;
}
</style>
