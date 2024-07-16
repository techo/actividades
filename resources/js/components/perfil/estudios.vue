<template>
    <div>
        <section>
            <div class="card w-100 m-2" v-for="(estudio, index) in estudios_persona">
                <cardEditDelete 
                :identifier="estudio.idEstudio"
                :nivelDeEstudios = "estudio.nivelDeEstudios"
                :header="estudio.disciplina_academica"
                :headerLabel="$t('frontend.disciplina_academica')"
                :title="estudio.institucion_educativa"
                :idInstitucionEducativa="estudio.idInstitucionEducativa"
                :titleLabel="$t('frontend.institucion_educativa')"
                :idPaisInstitucion="estudio.idPaisInstitucion"
                :text="estudio.descripcion_educacion" 
                :textLabel="$t('frontend.descripcion_educacion')" 
                :paises="paisesConInstitucionesEducativas" 
                @deleteCard="deleteEstudio(index)"
                @saveCard="saveEstudio"
                />
            </div>
            <div class="card w-100 m-2" v-if="creandoEstudio">
                <cardEditDelete 
                :headerLabel="$t('frontend.disciplina_academica')" 
                :titleLabel="$t('frontend.institucion_educativa')"
                :textLabel="$t('frontend.descripcion_educacion')" 
                :paises="paisesConInstitucionesEducativas" 
                :idInstitucionEducativa="-1"
                @createCard="createEstudio"
                :newCard="true"
                />
            </div>
            <div v-else class="row justify-content-center">
                <a @click="(creandoEstudio = true)" class="btn btn-secondary text-white">
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
    name: 'estudios',
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
            paisesConInstitucionesEducativas: [],
        }
        return data;
    },
    props: ['estudios', 'idPersona'],
    mounted: function () {
        this.formDirty = false;
        this.traer_paises();
    },
    watch: {

    },
    methods: {
        traer_paises: function () {
            axios.get('/ajax/paises/conInstitucionesEducativas').then(response => {
                this.paisesConInstitucionesEducativas = response.data
            })
        },
        createEstudio: function (data) {
            this.guardo = false;
            let form = {
                institucion_educativa: data.title,
                idInstitucionEducativa: data.idInstitucionEducativaSeleccionada,
                disciplina_academica: data.header,
                descripcion_educacion: data.text,
                nivelDeEstudios: data.nivelDeEstudios,
                idPaisInstitucion: data.idPaisInstitucion,
                idPersona: this.idPersona,
            }
            axios.post('/ajax/estudios', form).then(response => {
                this.guardo = true;
                this.formDirty = false;
                this.estudios_persona.push(form);
            }).catch((error) => {
            });
            this.creandoEstudio = false;
        },
        saveEstudio: function (data) {
            this.guardo = false;
            let form = {
                id: data.id,
                institucion_educativa: data.title,
                disciplina_academica: data.header,
                idInstitucionEducativa: data.idInstitucionEducativaSeleccionada,
                descripcion_educacion: data.text,
                nivelDeEstudios: data.nivelDeEstudios,
                idPaisInstitucion: data.idPaisInstitucion,
            }
            axios.put('/ajax/estudios', form).then(response => {
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
            this.creandoEstudio = false;
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
