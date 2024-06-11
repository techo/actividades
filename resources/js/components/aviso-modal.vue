<template>
    <div class="modal-dialog">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h4 class="modal-title">USO DESDE INSTAGRAM</h4>
            </div>
            <div class="modal-body text-center">
                <p> 
                    Para poder inscribirte a una actividad es necesario que entres a la plataforma usando un navegador como Chrome, Firefox o Safari
                </p>
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
        $('#aviso-modal').modal('show'); //sino pasan cosas raras con el scroll
    },
    watch: {
    },
    computed: {
    },
    methods: {
        cancelar() {
            this.hide();
        },
    }
}
</script>