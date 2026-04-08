<template>
    <div class="container mb-4" >
            <h4 class="my-2">{{ $t('frontend.actividades_destacadas') }}</h4> 
    <div class="carousel-container">
      <!-- Flecha Izquierda (oculta si hay solo una actividad) -->
        
      <button
        v-if="actividades.length > 1 && activeIndex>0"
        @click="prevSlide"
        class="flecha-izquierda"
      >
        &lt;
      </button>
  
      <div  v-if="actividades.length > 0" 
        class="carousel-slide" v-on:click="ir_a_actividad">
        <transition name="fade">
          <img
            :src="actividades[activeIndex].imagen_destacada"
            class="carousel-image"
            alt="Imagen destacada"
            :key="activeIndex"
          />
        </transition>
      </div>
  
      <!-- Flecha Derecha (oculta si hay solo una actividad) -->
      <button
        v-if="actividades.length > 1  && activeIndex<actividades.length-1"
        @click="nextSlide"
        class="flecha-derecha"
      >
        &gt;
      </button>
    </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      actividades: {
        type: Array,
        required: true,
      },
    },
    data() {
      return {
        activeIndex: 0, // Índice de la imagen actual
      };
    },
    methods: {
      nextSlide() {
        this.activeIndex = (this.activeIndex + 1) % this.actividades.length;
      },
      prevSlide() {
        this.activeIndex =
          (this.activeIndex - 1 + this.actividades.length) % this.actividades.length;
      },
      ir_a_actividad: function () {
        window.location.href = '/actividades/' + this.actividades[this.activeIndex].idActividad
        },
    },
  };
  </script>
  
  <style scoped>
  /* Contenedor del carrusel */
  .carousel-container {
    position: relative;
    width: 100%;
    max-width: 1200px; /* Ancho máximo opcional */
    margin: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 20px; /* Bordes redondeados */
  }
  
  /* Imagen destacada ajustada al ancho de la página */
  .carousel-slide {
    width: 100%;
    height: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }
  
  .carousel-image {
    width: 100%;
    height: auto;
    object-fit: contain; /* Ajuste sin cortar la imagen */
    border-radius: 20px; /* Bordes redondeados */
    transition: 0.5s ease all;
  }
  
  /* Flechas de navegación (ocultas si hay una sola actividad) */
  .flecha-izquierda,
  .flecha-derecha {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0);
    color: black;   
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    z-index: 10;
    transition: background 0.3s ease;
    transition: 0.2s ease all;
    outline: none;
  }
  
  .flecha-izquierda {
    left: -5px;
  }
  
  .flecha-derecha {
    right: -5px;
  }
  
  /* Efecto hover en botones */
  .flecha-izquierda:hover,
  .flecha-derecha:hover {
    background: rgba(0, 0, 0, 0.8);
  }
  
  /* Animación suave entre imágenes */
  .fade-enter-active, .fade-leave-active {
    transition: opacity 0.5s ease-in-out;
  }
  .fade-enter, .fade-leave-to {
    opacity: 0;
  }
  </style>
  