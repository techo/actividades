<template>
  <div class="col-md-4">
    <div class="card tarjeta p-1 m-2" v-on:click="ir_a_actividad" v-bind:style="{backgroundColor:actividad.tipo.color}">
        <div class="img-tarjeta mx-3">
            <div class="card-top m-1 p-0 ml-2">
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
            </div>
        </div>
        
      <div class="card-body px-0 pt-1 pb-1">
        <div style="width: 100%;">
            <img class="card-img-top px-3" :src="actividad.imagen_tarjeta ? actividad.imagen_tarjeta : actividad.tipo.imagen" alt="imagen actividad"
                v-bind:style="{ borderRadius: '15%', width: '15rem', height: '8.4rem' }">
            <div class=" texto-encima centrado">
                <span class="techo-titulo-card">{{ actividad.tipo.nombre }}</span><br>
            </div>
        </div>
        <p v-if="actividad.show_location" class="techo-titulo-card text-center pt-1" >{{ actividad.ubicacion }}</p>
        <h6 class="pt-1 text-center text-white px-1">{{ nombreActividadRecortado }}</h6>
      </div>
      <div class="card-footer px-0 pt-0 border-0" v-if="actividad.show_dates" style="width: 100%; font-size: 14px; margin: 0.5em 0; padding: 0.5em 0">
            <span class="col-sm-4"><i class="fas fa-calendar-alt"></i> <span style="padding-bottom: 5px">{{ actividad.fecha }}</span></span>
            <span class="col-sm-4"><i class="fas fa-clock"></i> {{ actividad.hora }}</span>
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
          },
          nombreActividadRecortado() {
            return this.actividad.nombreActividad.length > 50 ? this.actividad.nombreActividad.substring(0, 46) + '..' : this.actividad.nombreActividad;
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
    border-radius: 15%;
    text-align: center;
    height: 390px;
    background-color: var(--card-color); /* Esto aplica el color base */
    transition: background-color 0.3s ease; /* Transición suave */
}

div.tarjeta:hover {
    filter: brightness(1.2); /* Aumenta el brillo al pasar el mouse */
}


    div.card-top {
        height: 25px;
        border-radius: 4px 4px 0 0;
    }

    .img-tarjeta {
        position: relative;
        width: 100%;
        border: 0px;
        border-radius: 25%;
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
        width: 60%;
        border-radius: 10px 10px 10px 10px;
        -moz-border-radius: 10px 10px 10px 10px;
        -webkit-border-radius: 10px 10px 10px 10px;
    }
    
    .centrado{
        top: 25%;
        left: 50%;
        transform: translate(-50%, -50%);
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
