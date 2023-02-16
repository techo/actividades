<template>
    <div>
        <section>
            <div class="card w-100 m-2" v-for="(estudio, index) in estudios_persona">
                <cardEditDelete 
                :identifier="estudio.idEstudio"
                :header="estudio.titulo"
                :headerLabel="$t('frontend.titulo_educacion')"
                :title="estudio.institucion_educativa"
                :titleLabel="$t('frontend.institucion_educativa')"
                :subTitle="estudio.disciplina_academica" 
                :subTitleLabel="$t('frontend.disciplina_academica')" 
                :text="estudio.descripcion_educacion" 
                :textLabel="$t('frontend.descripcion_educacion')" 
                @deleteCard="deleteEstudio(index)"
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
            formDirty: false,
            message: {
                danger: false,
                text: ''
            },
            creandoEstudio: false,
        }
        return data;
    },
    props: ['estudios', 'idPersona'],
    mounted: function () {
        this.formDirty = false
    },
    watch: {

    },
    methods: {
        createEstudio: function (data) {
            this.guardo = false;
            let form = {
                titulo: data.header,
                institucion_educativa: data.title,
                disciplina_academica: data.subTitle,
                descripcion_educacion: data.text,
                idPersona: this.idPersona,
            }
            axios.post('/ajax/estudios', form).then(response => {
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
        },
        saveEstudio: function (data) {
            this.guardo = false;
            let form = {
                id: data.id,
                titulo: data.header,
                institucion_educativa: data.title,
                disciplina_academica: data.subTitle,
                descripcion_educacion: data.text,
            }
            axios.put('/ajax/estudios', form).then(response => {
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
        },
        deleteEstudio: function (index) {
            this.guardo = false;
            axios.delete('/ajax/estudios/'+this.estudios_persona[index].idEstudio).then(response => {
                this.guardo = true;
                this.formDirty = false;
                this.estudios_persona.splice(index, 1);
            }).catch((error) => {
            });
        },
    },
}
</script>
