<template>
    <div class="usuario-component">
        <div v-show="guardado" class="callout callout-success">
            <h4>{{ mensajeGuardado }}</h4>
        </div>
        <simplert ref="loading"></simplert>


        <div class="box">
            <!--<div class="box-header with-border">-->
            <!--<h3 class="box-title">Registrar usuario</h3>-->
            <!--</div>-->
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="grupo_sanguineo">Grupo Sanguineo</label>
                                    <input id="grupo_sanguineo" type="text" class="form-control"
                                        v-model="usuario.grupo_sanguinieo" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="cobertura_nombre">Nombre Cobertura Médica</label>
                                    <input id="cobertura_nombre" type="text" class="form-control"
                                        v-model="usuario.cobertura_nombre" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="cobertura_numero">Número Cobertura Médica</label>
                                    <input id="cobertura_numero" type="text" class="form-control"
                                        v-model="usuario.cobertura_numero" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="contacto_nombre">Nombre Contacto Emergencia</label>
                                    <input id="contacto_nombre" type="text" class="form-control"
                                        v-model="usuario.contacto_nombre" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="contacto_numero">Teléfono Contacto Emergencia</label>
                                    <input id="contacto_numero" type="text" class="form-control"
                                        v-model="usuario.contacto_telefono" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="contacto_relacion">Relación Contacto Emergencia</label>
                                    <input id="contacto_relacion" type="text" class="form-control"
                                        v-model="usuario.contacto_relacion" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" >
                    <div class="col-md-12">
                        <div class="form-group" v-if="(usuario.archivo_medico != null)">
                            <label for="archivo_medico">Archivo Medico</label>
                            <a :href="'/'+usuario.archivo_medico" target="_blank"> Ver Archivo </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: "usuario-ficha-form",
    props: ['propUsuario', 'edicion'],
    data() {
        return {
            usuario: {
                grupo_sanguinieo: "",
                cobertura_nombre: "",
                cobertura_numero: "",
                contacto_nombre: "",
                contacto_relacion: "",
                contacto_telefono: "",
                archivo_medico: "",
            },
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
    .usuario-ficha-component {
        margin-bottom: 120px;
    }
}
</style>