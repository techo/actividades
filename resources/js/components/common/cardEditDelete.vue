<template>
    <div>
        <div class="card-header">
            <div class="row align-items-center text-center" v-if="!editando">
                <h5 v-if="data.nivelDeEstudios == 'secundario'" class="col-8">
                    {{ $t('frontend.secundario') }}
                </h5>
                <h5 v-else class="col-8">
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
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ $t('frontend.nivel_de_estudios') }}</label>
                        <select id="nivelDeEstudios" v-model="data.nivelDeEstudios" class="form-control">
                            <option value="secundario">
                                {{ $t('frontend.secundario') }}
                            </option>
                            <option value="universitario">
                                {{ $t('frontend.universitario') }}
                            </option>
                            <option value="posgrado">
                                {{ $t('frontend.posgrado') }}
                            </option>
                        </select>
                    </div>
                    <div v-if="data.nivelDeEstudios && data.nivelDeEstudios != 'secundario'" class="col-md-9">
                        <label>{{ data.headerLabel }}</label>
                        <input class="form-control" v-model="data.header" />
                    </div>
                </div>
                
                
            </div>
        </div>
        <div class="card-body">
             <!-- institucion_educativa -->
            <div class="card-title">
                <h5 v-if="!editando"> {{ data.title }}
                </h5>
                <div v-else>
                    <div>
                        <label>{{ data.titleLabel }} *</label>
                        <div class="row">
                            <div class="col-md-4">
                                <select id="pais" v-model="idPaisSeleccionado" class="form-control m-1">
                                    <option disabled selected value="-1">{{ $t('backend.country') }}</option>
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
                                    <option disabled selected value="-1">{{ data.titleLabel }}</option>
                                    <option v-for="institucionEducativa in instituciones_educativas" v-bind:value="institucionEducativa.idInstitucionEducativa">
                                        {{ institucionEducativa.nombre }}
                                    </option>
                                    <option v-bind:value="0">
                                        {{ $t('frontend.other') }}
                                    </option>
                                </select>
                            </div>
                            <div v-else class="col-md-8">
                                <select id="pais" v-model="idPaisOtrosSeleccionado" class="form-control m-1">
                                    <option disabled selected value="-1">{{ $t('backend.country') }}</option>
                                    <option v-for="pais in paisesTodos" v-bind:value="pais.id">
                                        {{ pais.nombre }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div v-if="idPaisSeleccionado == 0 || data.idInstitucionEducativaSeleccionada == 0" >
                            <label> {{ $t('frontend.ingrese_institucion_educativa') }} </label>
                            <input class="form-control" v-model="data.title" />
                        </div>
                    </div>
                </div>
            </div>
             <!-- descripcion_educacion -->
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
                text: this.text,
                textLabel: this.textLabel,
                nivelDeEstudios: this.nivelDeEstudios,
                idPaisInstitucion: -1,
            },
            instituciones_educativas: [],
            idPaisSeleccionado: -1,
            paisesTodos: [],
            idPaisOtrosSeleccionado: -1,
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
        nivelDeEstudios: {
            type: String,
            default: ''
        },
        idPaisInstitucion: {
            type: Number,
            default: null
        },
    },
    mounted: function () {
        this.formDirty = false;
        if(this.idInstitucionEducativa == null){
            this.idPaisSeleccionado = 0;
            this.idPaisOtrosSeleccionado = this.idPaisInstitucion;
        }
        if(this.idInstitucionEducativa == 0){
            this.idPaisSeleccionado = this.idPaisInstitucion;
        }
        if (this.idInstitucionEducativa && this.idInstitucionEducativa > 0)
            this.traer_institucion_educativa_selccionada();
    },
    watch: {
        idPaisSeleccionado: function () {
            if (this.idPaisSeleccionado > 0){
                this.traer_instituciones_educativas();
            } else { 
                this.idInstitucionEducativaSeleccionada = 0;
                this.traer_paises_todos();
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
        traer_paises_todos: function () {
            axios.get('/ajax/paises').then(response => {
                this.paisesTodos = response.data
            })
        },
        editCard: function () {
            this.editando = true;
        },
        saveCard: function () {
            if (this.idPaisSeleccionado == 0){
                this.data.idPaisInstitucion = this.idPaisOtrosSeleccionado
                this.data.idInstitucionEducativaSeleccionada = null
            } else
                this.data.idPaisInstitucion = this.idPaisSeleccionado

            if(this.data.nivelDeEstudios == 'secundario')
                this.data.header = 'secundario';


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
