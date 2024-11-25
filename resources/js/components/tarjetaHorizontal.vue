<template>
    <div class="col-lg-6">
      <div class="card tarjeta" v-on:click="ir_a_actividad" v-bind:style="{backgroundColor:color}">
        <div class="card-body row">
          <div class="col-sm-6">
              <img class="card-img-top" :src="actividad.imagen_tarjeta ? actividad.imagen_tarjeta : actividad.tipo.imagen" alt="imagen actividad"
                  v-bind:style="{ borderRadius: '15px' , width: '9rem', height: '7rem'}">
          </div>
          <div class="col-sm-6">
            <h5 class="text-center text-white">{{ actividad.nombreActividad }}</h5>
            <p class="pt-1 text-center text-white px-1">{{ actividad.descripcion }}</p>
            <button type="button" 
                class="btn btn-primary bg-techo-blue" 
                @click="ir_a_actividad" >
                    {{ $t('frontend.postulate') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  
  </template>
  
  <script>
      export default {
          name: 'tarjeta-horizontal',
          props: ['actividad', 'color'],
          data () {
              return {
                  key: '',
              }
          },
          methods: {
              ir_a_actividad: function () {
                  window.location.href = '/actividades/' + this.actividad.idActividad
              },
          }
      }
  </script>
  
  <style>
  div.tarjeta {
      cursor: pointer;
      border: 0px;
      border-radius: 25px;
      text-align: center;
      background-color: var(--card-color); /* Esto aplica el color base */
      transition: background-color 0.3s ease; /* Transición suave */
  }
  
  div.tarjeta:hover {
      filter: brightness(1.2); /* Aumenta el brillo al pasar el mouse */
  }
  
      @media screen and (max-width: 767px) {
          .card-body img {
              aspect-ratio: 23 / 10;
          }
      }
  
      @media screen and (max-width: 576px) {
          .card-body img {
              aspect-ratio: 24 / 10;
          }
      }
  
      @media screen and (max-width: 410px) {
          .card-body img {
              aspect-ratio: 16 / 10;
          }
      }
  
      @media screen and (max-width: 350px) {
          .card-body img {
              aspect-ratio: 14 / 10;
          }
      }
  </style>
  