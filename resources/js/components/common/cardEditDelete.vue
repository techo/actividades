<template>
    <div>
        <div class="card-header">
            <div class="row" v-if="!editando">
                <h5 class="col-8">
                    {{ data.header }}
                </h5>

                <div class="col-4 text-right">
                    <span>
                        <a @click="editCard" class="btn btn-light m-1">
                            <i class="fa fa-edit"></i>
                        </a>
                        <a v-if="data.id" @click="deleteCard" class="btn btn-light m-1">
                            <i class="fa fa-trash"></i>
                        </a>
                    </span>
                </div>
            </div>
            <div v-else class="p-1">
                <label>{{ data.headerLabel }}</label>
                <input class="form-control" v-model="data.header" />
            </div>
        </div>
        <div class="card-body">

            <div class="card-title">
                <h5 v-if="!editando"> {{ data.title }} - {{ data.subTitle }}
                </h5>
                <div v-else>
                    <div>
                    <label>{{ data.titleLabel }} *</label>
                    <div class="row">
                        <div class="col-md-4">
                            <select id="pais" v-model="idPaisSeleccionado" class="form-control m-1">
                                <option v-bind:value="0">
                                    {{ $t('frontend.other') }}
                                </option>
                                <option v-for="pais in paises" v-bind:value="pais.id">
                                    {{ pais.nombre }}
                                </option>
                            </select>
                        </div>
                        <div v-if="idPaisSeleccionado" class="col-md-8">
                            <select id="institucionEducativa" v-model="data.idInstitucionEducativaSeleccionada" 
                            class="form-control m-1">
                                <option v-for="institucionEducativa in instituciones_educativas" v-bind:value="institucionEducativa.idInstitucionEducativa">
                                    {{ institucionEducativa.nombre }}
                                </option>
                                <option v-bind:value="0">
                                    {{ $t('frontend.other') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div v-if="idPaisSeleccionado == 0 || data.idInstitucionEducativaSeleccionada == 0" >
                        <label> {{ $t('frontend.ingrese_institucion_educativa') }} </label>
                        <input class="form-control" v-model="data.title" />
                    </div>
                </div>
                    <label>{{ data.subTitleLabel }}</label>
                    <input class="form-control" v-model="data.subTitle" />
                </div>
            </div>
            <div class="card-text">
                <div class="row">
                    <p v-if="!editando" class="col-md-10">
                        {{ data.text }}
                    </p>
                    <div v-else class="col-md-12">
                        <label>{{ data.textLabel }}</label>
                        <textarea class="form-control" v-model="data.text">
                            {{ data.text }}
                        </textarea>
                    </div>
                </div>
                <div class="row justify-content-center mt-4">
                    <span v-if="(editando && !newCard)">
                        <a @click="saveCard" class="btn btn-primary text-white p-1 m-1">
                            <i class="fa fa-save"></i>
                            {{ $t('frontend.save') }}
                        </a>
                    </span>

                    <span v-if="newCard">
                        <a @click="saveCard" class="btn btn-primary text-white p-1 m-1">
                            <i class="fa fa-save"></i>
                            {{ $t('frontend.save') }}
                        </a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: 'cardEditDelete',
    data: function () {
        var data = {
            editando: this.newCard,
            data: {
                id: this.identifier,
                header: this.header,
                headerLabel: this.headerLabel,
                title: this.title,
                titleLabel: this.titleLabel,
                idInstitucionEducativaSeleccionada: this.idInstitucionEducativa,
                subTitle: this.subTitle,
                subTitleLabel: this.subTitleLabel,
                text: this.text,
                textLabel: this.textLabel,

            },
            instituciones_educativas: [],
            idPaisSeleccionado: null,
        }
        return data;
    },
    props: {
        identifier: {
            type: Number,
            default: null
        },
        idInstitucionEducativa: {
            type: Number,
            default: null
        },
        header: {
            type: String,
            default: ""
        },
        title: {
            type: String,
            default: ""
        },
        subTitle: {
            type: String,
            default: ""
        },
        text: {
            type: String,
            default: ""
        },
        headerLabel: {
            type: String,
            default: ""
        },
        titleLabel: {
            type: String,
            default: ""
        },
        subTitleLabel: {
            type: String,
            default: ""
        },
        textLabel: {
            type: String,
            default: ""
        },
        newCard: {
            type: Boolean,
            default: false
        },
        paises: {
            type: Array
        },
    },
    mounted: function () {
        this.formDirty = false;
        if(this.idInstitucionEducativa == 0)
            this.idPaisSeleccionado = 0
        if (this.idInstitucionEducativa && this.idInstitucionEducativa > 0)
            this.traer_institucion_educativa_selccionada();
    },
    watch: {
        idPaisSeleccionado: function () {
            if (this.idPaisSeleccionado > 0){
                this.traer_instituciones_educativas();
            } else { 
                this.idInstitucionEducativaSeleccionada = 0;
            }
        },
        'data.idInstitucionEducativaSeleccionada': function () {
            if (this.idPaisSeleccionado > 0){
                const selected = this.instituciones_educativas.find(
                    objeto => objeto.idInstitucionEducativa === this.data.idInstitucionEducativaSeleccionada
                );
                this.data.title = selected.nombre;
            } else {
                this.idInstitucionEducativaSeleccionada = 0;
            }
        },
    },
    methods: {
        traer_instituciones_educativas: function () {
            axios.get('/ajax/institucionEducativa/pais/'+this.idPaisSeleccionado).then(response => {
                this.instituciones_educativas = response.data
            })
        },
        traer_institucion_educativa_selccionada: function () {
            axios.get('/ajax/institucionEducativa/'+this.idInstitucionEducativa).then(response => {
                this.idPaisSeleccionado = response.data.idPais
            })
        },
        editCard: function () {
            this.editando = true;
        },
        saveCard: function () {
            if (this.data.id){
                this.$emit('saveCard', this.data);
            } else {
                this.$emit('createCard', this.data);
            }
            this.editando = false;
        },
        deleteCard: function () {
            this.$emit('deleteCard');
        },
    },
    computed: {
    }
}
</script>
