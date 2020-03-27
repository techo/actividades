<template>
    <div :class="{ 'modal':true, 'fade': true }" :style="{}" id="inscribir-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">SUSPENCIÓN DE ACTIVIDADES</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()" >
                        <span aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">

                    <p> Contemplando la prevención y bienestar de los y las habitantes de las comunidades en las que trabajamos, nuestro voluntariado, equipo rentado, donantes y demás de audiencias con las que trabajamos y colaboramos día a día; desde TECHO se establece la suspensión de todas las actividades masivas presenciales hasta nuevo aviso. <br>  <br> 
                        A continuación podrás encontrar actividades virtuales que continuan activas.

                     </p>

                </div>
            </div>
        </div>
    </div>
</template>


<script>


export default {
    props: [],
    data: function () {
        return {
        }
    },
    mounted() { 
        $('#inscribir-modal').modal('show'); //sino pasan cosas raras con el scroll
    },
    watch: {
    },
    computed: {
    },
    methods: {
        cancelar() {
            this.reset();
            this.reset_errors();
            this.hide();
        },
    }
}
</script>