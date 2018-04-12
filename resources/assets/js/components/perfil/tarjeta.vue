<template>
     <div class="col-sm-4">
        <div class="card tarjeta p-3">
            <simplert ref="confirmar"></simplert>
            <img class="card-img-top" v-on:click="ir_a_actividad" src="/img/tarjeta-1.jpg" alt="Card image cap" >
            <div class="card-body px-0">
                <p class="techo-titulo-card">{{ actividad.tipo.nombre }}</p>
                <h5 class="card-title" v-on:click="ir_a_actividad">{{ actividad.nombreActividad | truncate(30) }}</h5>
                <div>
                    <hr>
                    <span class="col-sm-4"><i class="fas fa-calendar-alt"></i> <span style="padding-bottom: 5px">{{ actividad.fecha }}</span></span>
                    <span class="col-sm-4"><i class="fas fa-clock"></i> {{ actividad.hora }}</span>
                    <span class="col-sm-4"><i class="fas fa-map-marker-alt"></i> {{ actividad.localidad | ubicacion }}</span>
                    <hr>
                </div>
                <p class="card-text">{{ actividad.descripcion | truncate(100) }}</p>
                <div class="">
                    <a class="btn btn-success text-light font-weight-bold" @click="desincribir(actividad.idActividad)">Desinscribirme</a>
                </div>
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
            },

            ubicacion: function (ubicacion) {
                return ubicacion === null ? "" : ubicacion.localidad;
            }
        },
        methods: {
          ir_a_actividad: function () {
            window.location.href = '/actividades/' + this.actividad.idActividad
          },
            desincribir: function (idActividad) {
                var self = this;
                self.$refs.confirmar.openSimplert({
                    title:'DESINSCRIBIRME DE ACTIVIDAD',
                    message:"Estás por desinscribirte de la actividad " + self.actividad.nombreActividad + ", se borrarán tus datos para participar. Puedes inscribirte cuando desees. ¿Deseas continuar?",
                    useConfirmBtn: true,
                    isShown: true,
                    disableOverlayClick: true,
                    customClass: 'confirmar',
                    customCloseBtnText: 'CANCELAR', //string -- close button text
                    customCloseBtnClass: '', //string -- custom class for close button
                    customConfirmBtnText: 'SI, DESINSCRIBIRME', //string -- confirm button text
                    customConfirmBtnClass: '', //string -- custom class for confirm button
                    onConfirm: function() {
                        axios.delete('/ajax/usuario/inscripciones/' + idActividad).then(response => {
                            self.$parent.traer_actividades()
                            self.$parent.borro = true;
                            setTimeout(function(){
                                self.$parent.borro = false;
                            }, 3000)
                        })
                    }
                })
                /*
                */
            }
        }
    }
</script>
<style>

div.tarjeta {
    cursor: pointer;
    border: 0px;
    text-align: center;
}

.confirmar > div {
    min-width: 60%;
}
.confirmar .simplert__icon {
    display: none;
}

</style>