<template>
  <div class="col-md-4">
    <div class="card tarjeta p-2 m-2" v-on:click="ir_a_actividad" v-bind:style="{backgroundColor:actividad.tipo.color}">
        <div class="img-tarjeta">
            <div class="card-top" v-bind:style="{backgroundColor:actividad.tipo.color}" ></div>
            <span
                v-show="actividad.estadoInscripcion"
                :class="{ 
                    'inscripto': true, 
                    'badge': true, 
                    'badge-pill': true, 
                    'badge-danger': actividad.estadoInscripcion == 'confirmation_date_is_closed', 
                    'badge-primary': actividad.estadoInscripcion == 'confirm_by_paying', 
                    'badge-warning': actividad.estadoInscripcion == 'waiting_for_confirmation',
                    'badge-success': actividad.estadoInscripcion == 'confirmed',
                }" >
                {{ $t('frontend.' + actividad.estadoInscripcion) }}
            </span>
            <span v-show="!actividad.estadoInscripcion && !cuposLlenos && pocosCupos" class="pocos-cupos badge badge-pill badge-warning">{{ $t('frontend.limit_about_to_be_reached') }}</span>
            <span v-show="!actividad.estadoInscripcion && cuposLlenos" class="sin-cupos badge badge-pill badge-danger">{{ $t('frontend.activity_full') }}</span>
            <span v-show="!actividad.estadoInscripcion && fechaLimitePagoVencida" class="inscripto badge badge-pill badge-danger">{{ $t('frontend.confirmation_date_is_closed') }}</span>
            <!-- <img class="card-img-top" :src="actividad.tipo.imagen" alt="imagen actividad"> -->
        </div>
        
      <div class="card-body px-0 pt-1">
        <div style="width: 100%; border-bottom: #b7babf thin solid;">
            <img class="card-img-top px-2" :src="actividad.tipo.imagen" alt="imagen actividad" v-bind:style="{borderRadius:'6%'} ">
            <div class=" texto-encima centrado">
                <span class="col-sm-6 techo-titulo-card" v-bind:style="{color:actividad.tipo.color}" >{{ actividad.tipo.nombre }}</span><br>
            </div>
        </div>
        <p class="techo-titulo-card" v-bind:style="{color:actividad.tipo.color}" >{{ actividad.ubicacion }}</p>
        <h5 class="card-title text-center">{{ actividad.nombreActividad }}</h5>
        <div style="width: 100%; border-bottom: #b7babf thin solid; font-size: 14px; margin: 0.5em 0; padding: 0.5em 0">
            <span class="col-sm-4"><i class="fas fa-calendar-alt"></i> <span style="padding-bottom: 5px">{{ actividad.fecha }}</span></span>
            <span class="col-sm-4"><i class="fas fa-clock"></i> {{ actividad.hora }}</span>
            <!-- <span class="col-sm-4"><i class="fas fa-map-marker-alt"></i> {{ actividad.ubicacion }}</span> -->
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
          },
          fechaLimitePagoVencida: function () {
            let hoy = moment(moment().format("MM-DD-YYYY"),"MM-DD-YYYY");
            let fecha_limite = moment(this.actividad.fechaLimitePago, "DD-MM-YYYY");
            return this.actividad.pago == 1 && this.actividad.fechaLimitePago != ''  && fecha_limite < hoy;
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
        }
    }
</script>

<style>

    div.tarjeta {
        cursor: pointer;
        border: 0px;
        border-radius: 8% 8%;
        text-align: center;
    }

    div.card-top {
        height: 40px;
        border-radius: 4px 4px 0 0;
    }

    .img-tarjeta {
        position: relative;
        width: 100%;
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
        /*position: absolute;*/
        bottom: 5px;
        left: 5px;
        position: relative;
        display: inline-block;
        text-align: center;
    }
    .texto-encima{
        background: rgba(51, 51, 51, 0.9);
        position: absolute;
        top: 22em;
        right: 1em;
        width: 80%;
        border-radius: 10px 10px 10px 10px;
        -moz-border-radius: 10px 10px 10px 10px;
        -webkit-border-radius: 10px 10px 10px 10px;
    }
    
    .centrado{
        top: 20%;
        left: 50%;
        transform: translate(-50%, -50%);
    }


</style>
