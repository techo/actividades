<template>
  <div class="col-md-4">
    <div class="card" style="cursor: pointer;" v-on:click="ir_a_actividad">
      <img class="card-img-top" src="/img/tarjeta-1.jpg" alt="Card image cap" >
      <div class="card-body px-0">
        <p class="techo-titulo-card">{{ actividad.tipo.nombre }}</p>
        <h5 class="card-title">{{ actividad.nombreActividad | truncate(30) }}</h5>
        <div style="width: 100%; border-top: #b7babf thin solid;border-bottom: #b7babf thin solid; font-size: 14px; margin: 0.5em 0; padding: 0.5em 0">
            <span class="col-sm-4"><i class="fas fa-calendar-alt"></i> <span style="padding-bottom: 5px">{{ actividad.fecha }}</span></span>
            <span class="col-sm-4"><i class="fas fa-clock"></i> {{ actividad.hora }}</span>
            <span class="col-sm-4"><i class="fas fa-map-marker-alt"></i> {{ actividad.localidad.localidad }}</span>
        </div>
        <p class="card-text">{{ actividad.descripcion | truncate(100) }}</p>
      </div>
    </div>
  </div>

</template>

<script>
    export default {
        name: 'tarjeta',
        props: ['actividad'],
        data () {
            return {
                key: ''
            }
        },

        filters: {
            truncate: function(string, value) {
                if(!string) return '';
                return string.substr(0,value) + '...';
            }
        },
        methods: {
          ir_a_actividad: function () {
            window.location.href = '/actividades/' + this.actividad.idActividad
          }
        }
    }
</script>