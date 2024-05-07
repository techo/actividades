<template>
    <div class="usuario-component">
        <div v-show="guardado" class="callout callout-success">
            <h4>{{ mensajeGuardado }}</h4>
        </div>
        <simplert ref="loading"></simplert>


        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        
                    </div>
                    <div class="col-md-6">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: "usuario-estudios-form",
    props: ['propUsuario', 'edicion'],
    data() {
        return {
            estudios: [],
            readonly: !this.edicion,
            guardado: false,
            mensajeGuardado: '',
            validationErrors: {}
        }
    },
    created() {

        if (this.propUsuario) {
            this.usuario = JSON.parse(this.propUsuario);
        }
    },
}
</script>

<style scoped>
@media (max-width: 768px) {
    .usuario-estudios-component {
        margin-bottom: 120px;
    }
}
</style>