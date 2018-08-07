<template>
  <div class="col-md-4">
    <div class="card tarjeta p-3" v-on:click="ir_a_actividad">
        <div class="img-tarjeta">
            <span v-show="inscripto" class="inscripto badge badge-pill badge-success">¡Ya estás inscripto!</span>
            <span v-show="preinscripto" class="inscripto badge badge-pill badge-primary">¡Confirma con tu donación!</span>
            <span v-show="!inscripto && !cuposLlenos && pocosCupos" class="pocos-cupos badge badge-pill badge-warning">¡Quedan pocos cupos!</span>
            <span v-show="!inscripto && cuposLlenos" class="sin-cupos badge badge-pill badge-danger">¡Se llenaron los cupos!</span>
            <img class="card-img-top" :src="actividad.tipo.imagen" alt="imagen actividad">
        </div>
      <div class="card-body px-0">
        <p class="techo-titulo-card">{{ actividad.tipo.nombre }}</p>
        <h5 class="card-title text-left">{{ actividad.nombreActividad }}</h5>
        <div style="width: 100%; border-top: #b7babf thin solid;border-bottom: #b7babf thin solid; font-size: 14px; margin: 0.5em 0; padding: 0.5em 0">
            <span class="col-sm-4"><i class="fas fa-calendar-alt"></i> <span style="padding-bottom: 5px">{{ actividad.fecha }}</span></span>
            <span class="col-sm-4"><i class="fas fa-clock"></i> {{ actividad.hora }}</span>
            <span class="col-sm-4"><i class="fas fa-map-marker-alt"></i> {{ actividad.localidad | ubicacion }}</span>
        </div>
        <p class="card-text text-left">{{ actividad.descripcion | truncate(100) }}</p>
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
                key: '',
            }
        },
        computed: {
          inscripto: function () {
              let userId = this.$parent.$parent.$refs.login.user.id;
              if(userId != ""){
                  let obj = this.findObjectByKey(this.actividad.inscriptos, 'idPersona', userId);
                  if (obj !== null && obj.item.estado !== 'Pre-Inscripto') {
                      return true;
                  }
              }
              return false;
          },
          preinscripto: function () {
              let userId = this.$parent.$parent.$refs.login.user.id;
              if(userId != ""){
                  let obj = this.findObjectByKey(this.actividad.inscriptos, 'idPersona', userId);
                  if (obj !== null && obj.item.estado === 'Pre-Inscripto') {
                      return true;
                  }
              }
              return false;
          },
          pocosCupos: function(){
              let umbral = 0.9;
              let porcentajeActual;
              if (this.actividad.limiteInscripciones === 0) {
                  return false;
              }
              porcentajeActual = this.actividad.cantInscriptos / this.actividad.limiteInscripciones;
              return porcentajeActual >= umbral ;
          },
          cuposLlenos: function () {
              return this.actividad.cuposRestantes <= 0 && this.actividad.limiteInscripciones !== 0;
          }
        },
        filters: {
            truncate: function(string, value) {
                if(!string) return '';
                return string.substr(0,value) + '...';
            },

            ubicacion: function (ubicacion) {
                return ubicacion === null ? "" : ubicacion.localidad;
            }
        },
        methods: {
            ir_a_actividad: function () {
                window.location.href = '/actividades/' + this.actividad.idActividad
            },
            findObjectByKey(array, key, value) {
                for (let i = 0; i < array.length; i++) {
                    if (array[i][key] === value) {
                        return {
                            'item': array[i],
                            'index': i
                        };
                    }
                }
                return null;
            },

        }
    }
</script>

<style>

    div.tarjeta {
        cursor: pointer;
        border: 0px;
        text-align: center;
    }

    .img-tarjeta {
        position: relative;
    }

    .img-tarjeta .inscripto {
        position: absolute;
        top: 5px;
        left: 5px;
    }

    .img-tarjeta .sin-cupos {
        position: absolute;
        bottom: 5px;
        left: 5px;
    }

    .img-tarjeta .pocos-cupos {
        position: absolute;
        bottom: 5px;
        left: 5px;
    }

</style>