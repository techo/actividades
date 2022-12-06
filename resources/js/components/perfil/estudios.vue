<template>
    <div>
        <section>
            <div class="card w-100 m-2" v-for="estudio in estudios_persona">
                <cardEditDelete 
                :header="estudio.titulo"
                :headerLabel="$t('frontend.titulo_educacion')"
                :title="estudio.institucion_educativa"
                :titleLabel="$t('frontend.institucion_educativa')"
                :subTitle="estudio.disciplina_academica" 
                :subTitleLabel="$t('frontend.disciplina_academica')" 
                :text="estudio.descripcion_educacion" 
                :textLabel="$t('frontend.descripcion_educacion')" 
                @deleteCard="deleteEstudio"
                @saveCard="saveEstudio"
                />
            </div>
            <div class="card w-100 m-2" v-if="creandoEstudio">
                <cardEditDelete 
                :headerLabel="$t('frontend.titulo_educacion')"
                :titleLabel="$t('frontend.institucion_educativa')"
                :subTitleLabel="$t('frontend.disciplina_academica')" 
                :textLabel="$t('frontend.descripcion_educacion')" 
                @createCard="createEstudio"
                :newCard="true"
                />
            </div>
            <div v-else class="row justify-content-center">
                <a @click="(creandoEstudio = true)" class="btn btn-primary text-white">
                    <i class="fa fa-plus"></i>
                    {{ $t('frontend.new') }}
                </a>
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
            estudios_persona: this.estudios,
            archivo_medico: null,
            formDirty: false,
            message: {
                danger: false,
                text: ''
            },
            creandoEstudio: false,
        }
        return data;
    },
    props: ['estudios'],
    mounted: function () {
        this.formDirty = false
    },
    watch: {

    },
    methods: {
        addEstudio: function () {
            this.guardo = false;
            axios.post('/ajax/estudios', this.estudios).then(response => {
                this.submitFile();
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
        },
        editEstudio: function () {
            this.guardo = false;
            axios.post('/ajax/estudios', this.estudios).then(response => {
                this.submitFile();
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
        },
        deleteEstudio: function () {
            this.guardo = false;
            axios.post('/ajax/estudios', this.estudios).then(response => {
                this.submitFile();
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
        },
    },
}
</script>
