<template>
    <div>
        <section>
            <div class="row">
                <div class="col-md-4">
                    <label>{{ $t('frontend.grupo_sanguinieo') }}</label>
                    <input type="text" class="form-control" name="grupo_sanguinieo" id="grupo_sanguinieo"
                        v-model="ficha.grupo_sanguinieo">
                </div>
                <div class="col-md-4">
                    <label>{{ $t('frontend.cobertura_nombre') }}</label>
                    <input type="text" class="form-control" name="cobertura_nombre" id="cobertura_nombre"
                        v-model="ficha.cobertura_nombre">
                </div>
                <div class="col-md-4">
                    <label>{{ $t('frontend.cobertura_numero') }}</label>
                    <input type="text" class="form-control" name="cobertura_numero" id="cobertura_numero"
                        v-model="ficha.cobertura_numero">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <label>{{ $t('frontend.contacto_nombre') }}</label>
                    <input type="text" class="form-control" name="contacto_nombre" id="contacto_nombre"
                        v-model="ficha.contacto_nombre">
                </div>
                <div class="col-md-4">
                    <label>{{ $t('frontend.contacto_telefono') }}</label>
                    <input type="text" class="form-control" name="contacto_telefono" id="contacto_telefono"
                        v-model="ficha.contacto_telefono">
                </div>
                <div class="col-md-4">
                    <label>{{ $t('frontend.contacto_relacion') }}</label>
                    <input type="text" class="form-control" name="contacto_relacion" id="contacto_relacion"
                        v-model="ficha.contacto_relacion">
                </div>
                
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <label >{{ $t('frontend.archivo_medico') }}</label>
                    <a v-if="ficha.archivo_medico != null" :href="ficha.archivo_medico" target="_blank"> {{ $t('frontend.ver_adjunto') }}</a>
                    <button class="btn btn-light" @click="updateArchivo = true" ><i class="fa fa-edit"></i></button>
                    <input v-if="(ficha.archivo_medico == null || updateArchivo)" type="file" class="form-control" @change="guardar_archivo" ref="archivo_medico">
                </div>
            </div>
            <p class="text-muted mt-2">
                {{ $t('frontend.acepta_terminos') }}
            </p>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-check form-inline mb-2">
                        <input v-model="ficha.confirma_datos" class="form-check-input" type="checkbox"
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
            formDirty: false,
            error: false,
            message: {
                danger: false,
                text: ''
            },
            updateArchivo: false,
        }
        return data;
    },
    props: ['fichaMedica'],
    created: function () {
        if (this.ficha == null){
            this.ficha = {
              'contacto_nombre' : "",
              'contacto_telefono' : "",
              'contacto_relacion' : "",
              'grupo_sanguinieo' : "",
              'cobertura_nombre' : "",
              'cobertura_numero' : "",
              'confirma_datos' : "",
              'archivo_medico' : null
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
                this.submitFile();
                this.guardo = true;
                this.formDirty = false;
                this.$emit('guardado');
            }).catch((error) => {
                this.error = true;
            });
        },
        submitFile: function () {
            this.guardo = false;
            const formData = new FormData();
            formData.append('archivo_medico', this.archivo_medico);
            const headers = { 'Content-Type': 'multipart/form-data' };
            axios.post('/ajax/fichaMedica/archivo_medico', formData, { headers }).then(response => {
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
        },
        guardar_archivo(event) {
            console.log(this.$refs.archivo_medico.files[0]);
            this.archivo_medico = this.$refs.archivo_medico.files[0];
        },


    },
    computed: {

    }
}
</script>
