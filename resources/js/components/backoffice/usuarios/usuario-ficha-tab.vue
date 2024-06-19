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
                                    <label for="grupo_sanguineo">{{ $t('backend.blood_group') }}</label>
                                    <input id="grupo_sanguineo" type="text" class="form-control"
                                        v-model="usuario.grupo_sanguinieo" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="cobertura_nombre">{{ $t('backend.name') }} {{ $t('backend.medical_coverage') }}</label>
                                    <input id="cobertura_nombre" type="text" class="form-control"
                                        v-model="usuario.cobertura_nombre" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="cobertura_numero">{{ $t('backend.number') }} {{ $t('backend.medical_coverage') }}</label>
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
                                    <label for="contacto_nombre">{{ $t('backend.name') }} {{ $t('backend.emergency_contact') }}</label>
                                    <input id="contacto_nombre" type="text" class="form-control"
                                        v-model="usuario.contacto_nombre" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="contacto_numero">{{ $t('backend.phone') }} {{ $t('backend.emergency_contact') }}</label>
                                    <input id="contacto_numero" type="text" class="form-control"
                                        v-model="usuario.contacto_telefono" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="contacto_relacion">{{ $t('backend.relationship') }} {{ $t('backend.emergency_contact') }}</label>
                                    <input id="contacto_relacion" type="text" class="form-control"
                                        v-model="usuario.contacto_relacion" :disabled="readonly">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" >
                    <div class="col-md-6">
                        <div class="form-group" v-if="(usuario.alergias != null)">
                            <label for="alergias">{{ $t('backend.allergies') }}</label>
                            <input id="alergias" type="text" class="form-control"
                                        v-model="usuario.alergias" :disabled="readonly">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" v-if="(usuario.alimentacion != null)">
                            <label for="alimentacion">{{ $t('backend.diet') }}</label>
                            <input id="alimentacion" type="text" class="form-control"
                                        v-model="usuario.alimentacion" :disabled="readonly">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" v-if="(usuario.vacunacion_covid != null)">
                            <label for="vacunacion_covid">{{ $t('backend.covid_vaccination') }}</label>
                            <input id="vacunacion_covid" type="text" class="form-control"
                                        v-model="usuario.vacunacion_covid" :disabled="readonly">
                        </div>
                    </div>
                </div>
                <div class="row" >
                    <div class="col-md-6">
                        <div class="form-group" v-if="(usuario.documento_frente != null)">
                            <label for="documento_frente">{{ $t('backend.document_front') }}</label>
                            <a :href="'/'+usuario.documento_frente" target="_blank"> {{ $t('backend.view_front') }} </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" v-if="(usuario.documento_dorso != null)">
                            <label for="documento_dorso">{{ $t('backend.document_back') }}</label>
                            <a :href="'/'+usuario.documento_dorso" target="_blank"> {{ $t('backend.view_back') }} </a>
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
                alergias: "",
                vacunacion_covid: "",
                alimentacion: "",
                documento_frente: "",
                documento_dorso: "",
                archivo_medico: "",
            },
            readonly: !this.edicion,
            guardado: false,
            mensajeGuardado: '',
            validationErrors: {}
        }
    },
    created() {
        this.usuario = JSON.parse(this.propUsuario);
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