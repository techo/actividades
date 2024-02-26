<template>
    <div>
        <section>
            <p class="text-muted mt-2">
                {{ $t('frontend.acepta_terminos') }}
            </p>
            <div class="row" v-show="(!campos || campos.grupo_sanguinieo)">
                <div class="col-md-4">
                    <label>{{ $t('frontend.grupo_sanguinieo') }}</label>
                    <select id="localidad" v-model="ficha.grupo_sanguinieo" class="form-control">
                        <option v-for="grupo_sanguineo in grupo_sanguineos" v-bind:value="grupo_sanguineo">
                            {{ grupo_sanguineo }}
                        </option>
                    </select>
                </div>
            </div>
            <div v-show="(!campos || campos.cobertura_medica)">
                <h6 class="mt-4">{{ $t('frontend.cobertura_medica') }}</h6>
                <div class="row">
                    <div class="col-md-4">
                        <label>{{ $t('frontend.cobertura_tipo') }}</label>
                        <select id="cobertura_tipo" v-model="ficha.cobertura_tipo" class="form-control">
                            <option v-for="cobertura_tipo in cobertura_tipos" v-bind:value="cobertura_tipo">
                                {{ $t('frontend.'+cobertura_tipo) }}
                            </option>
                        </select>
                    </div>
                    <div class="col-md-4" v-show="ficha.cobertura_tipo == 'cobertura_paga'">
                        <label>{{ $t('frontend.cobertura_nombre') }}</label>
                        <input type="text" class="form-control" name="cobertura_nombre" id="cobertura_nombre"
                            v-model="ficha.cobertura_nombre">
                    </div>
                    <div class="col-md-4" v-show="ficha.cobertura_tipo == 'cobertura_paga'">
                        <label>{{ $t('frontend.cobertura_numero') }}</label>
                        <input type="text" class="form-control" name="cobertura_numero" id="cobertura_numero"
                            v-model="ficha.cobertura_numero">
                    </div>
                </div>
            </div>

            <div v-show="(!campos || campos.contacto_emergencia)">
                <h6 class="mt-4">{{ $t('frontend.contacto_emergencia') }}</h6>
                <div class="row">
                    <div class="col-md-4">
                        <label>{{ $t('frontend.name') }}</label>
                        <input type="text" class="form-control" name="contacto_nombre" id="contacto_nombre"
                            v-model="ficha.contacto_nombre">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $t('frontend.telephone') }}</label>
                        <input type="text" class="form-control" name="contacto_telefono" id="contacto_telefono"
                            v-model="ficha.contacto_telefono">
                    </div>
                    <div class="col-md-4">
                        <label>{{ $t('frontend.contacto_relacion') }}</label>
                        <input type="text" class="form-control" name="contacto_relacion" id="contacto_relacion"
                            v-model="ficha.contacto_relacion">
                    </div>                    
                </div>
            </div>

            <div v-show="(!campos || campos.documento_identidad)">
                <h6 class="mt-4">{{ $t('frontend.documents') }}</h6>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label >{{ $t('frontend.documento_identidad_frente') }}</label>
                        <a v-if="ficha.documento_frente != null" :href="'/'+ficha.documento_frente" target="_blank"> {{ $t('frontend.ver_frente') }}</a>
                        <button v-if="!(updateDocumentoFrente || ficha.documento_frente == null)" class="btn btn-light" @click="updateDocumentoFrente = true" ><i class="fa fa-edit"></i></button>
                        <input v-if="(ficha.documento_frente == null || updateDocumentoFrente)" type="file" class="form-control" @change="guardar_documento_frente" ref="documento_frente">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label >{{ $t('frontend.documento_identidad_dorso') }}</label>
                        <a v-if="ficha.documento_dorso != null" :href="ficha.documento_dorso" target="_blank"> {{ $t('frontend.ver_dorso') }}</a>
                        <button  v-if="!(updateDocumentoDorso || ficha.documento_dorso == null)" class="btn btn-light" @click="updateDocumentoDorso = true" ><i class="fa fa-edit"></i></button>
                        <input v-if="(ficha.documento_dorso == null || updateDocumentoDorso)" type="file" class="form-control" @change="guardar_documento_dorso" ref="documento_dorso">
                    </div>
                </div>
            </div>
            <!-- <div class="row mt-2">
                <div class="col-md-12">
                    <label>{{ $t('frontend.archivo_medico') }}</label>
                    <a v-if="ficha.archivo_medico != null" :href="ficha.archivo_medico" target="_blank"> {{ $t('frontend.ver_adjunto') }}</a>
                    <button v-if="!(updateArchivo || ficha.archivo_medico == null)" class="btn btn-light" @click="updateArchivo = true" ><i class="fa fa-edit"></i></button>
                    <input 
                        v-if="(ficha.archivo_medico == null || updateArchivo)" 
                        type="file" 
                        class="form-control" 
                        @change="guardar_archivo" 
                        ref="archivo_medico">
                </div>
            </div> -->
            <div v-show="(!campos || (campos.ficha_alergias || campos.ficha_alimentacion))">
            
                <h6 class="mt-4">{{ $t('frontend.ficha_otros') }}</h6>
                <div class="row">
                    <div class="col-md-6"  v-show="(!campos || campos.ficha_alergias)">
                        <label>{{ $t('frontend.ficha_alergias') }}</label>
                        <input type="text" class="form-control" name="alergias" id="alergias"
                            v-model="ficha.alergias">
                    </div>
                    <div class="col-md-6" v-show="(!campos || campos.ficha_alimentacion)">
                        <label>{{ $t('frontend.ficha_alimentacion') }}</label>
                        <input type="text" class="form-control" name="alimentacion" id="alimentacion"
                            v-model="ficha.alimentacion">
                    </div>
                </div>
            </div>
            <h6 class="mt-4">{{ $t('frontend.ficha_confirma_datos') }}</h6>
            <div class="row mt-2">
                <div class="col-md-12">
                    <div class=" pl-0 form-check form-inline mb-2">
                        <input v-model="ficha.confirma_datos" class="form-check-input pl-0" type="checkbox"
                            id="confirma_datos">
                        <label class="form-check-label" for="confirma_datos">
                            {{ $t('frontend.confirma_datos') }}
                        </label>
                    </div>
                </div>
            </div>
            <div class="row alert alert-danger" v-show='error'>
                <strong>{{ $t('frontend.changes_required_error') }}</strong>
            </div>
            <div class="row alert alert-success" v-show='guardo'>
                <strong>{{ $t('frontend.changes_success') }}</strong>
            </div>
            
            <div class="row mt-2">
                <div class="col-md-12">
                    <button v-if="ficha.confirma_datos" class="btn btn-primary" href="#" @click="guardarFicha()">{{
                            $t('frontend.save')
                    }}</button>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
import _ from 'lodash'

export default {
    name: 'perfil',
    data: function () {
        var data = {
            guardo: false,
            ficha: this.fichaMedica,
            archivo_medico: null,
            documento_frente: null,
            documento_dorso: null,
            formDirty: false,
            error: false,
            message: {
                danger: false,
                text: ''
            },
            updateArchivo: false,
            updateDocumentoDorso:false,
            updateDocumentoFrente:false,
            grupo_sanguineos: [
                'A+', 'A-', 'B+', 'B-', '0+', '0-'
            ],
            cobertura_tipos: [
                'cobertura_paga', 'salud_publica'
            ]
        }
        return data;
    },
    props: ['fichaMedica', 'campos'],
    created: function () {
        if (this.ficha == null){
            this.ficha = {
              'contacto_nombre' : "",
              'contacto_telefono' : "",
              'contacto_relacion' : "",
              'grupo_sanguinieo' : "",
              'tipo_cobertura': "",
              'cobertura_tipo' : "",
              'cobertura_nombre' : "",
              'cobertura_numero' : "",
              'alergias' : "",
              'alimentacion' : "",
              'confirma_datos' : "",
              'archivo_medico' : null,
              'documento_frente' : null,
              'documento_dorso' : null
            };
        }
    },
    mounted: function () {
        this.formDirty = false
    },
    methods: {
        guardarFicha: function () {
            this.guardo = false;
            axios.post('/ajax/fichaMedica', this.ficha).then(response => {
                this.submitFiles();
                this.guardo = true;
                this.formDirty = false;
                this.$emit('guardado');
            }).catch((error) => {
                this.error = true;
            });
        },
        submitFiles: function () {
            this.guardo = false;
            const formData = new FormData();
            formData.append('archivo_medico', this.archivo_medico);
            formData.append('documento_frente', this.documento_frente);
            formData.append('documento_dorso', this.documento_dorso);
            const headers = { 'Content-Type': 'multipart/form-data' };
            axios.post('/ajax/fichaMedica/archivo_medico', formData, { headers }).then(response => {
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
        },
        guardar_archivo(event) {
            this.archivo_medico = this.$refs.archivo_medico.files[0];
        },
        guardar_documento_frente(event) {
            this.documento_frente = this.$refs.documento_frente.files[0];
        },
        guardar_documento_dorso(event) {
            this.documento_dorso = this.$refs.documento_dorso.files[0];
        },

    },
    computed: {

    }
}
</script>
