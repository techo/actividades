<template>
    <div>
        <div class="card-header bg-techo-violet">
            <div class="row">
                <h5 class="col-md-10 d-flex align-items-center justify-content-center">
                    {{ equipo.rol+' - '+equipo.equipo.nombre }}
                </h5>

                <div class="col-md-2 text-right">
                    <span>
                        <a @click="editCard" class="btn bg-techo-violet m-1">
                            <i class="fa fa-edit"></i>
                        </a>
                    </span>
                    <span v-if="editando">
                        <a @click="saveCard" class="btn btn-primary bg-techo-blue m-1">
                            <i class="fa fa-save"></i> 
                        </a>
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-text">
                
                <div class="row  text-center">
                    <p class="col-md-6">
                        <label class="text-uppercase mx-2">{{ $t('frontend.despliegue') }}</label>
                        {{ equipo.despliegue }}
                    </p>
                    <p class="col-md-6">
                        <label class="text-uppercase mx-2">{{ $t('frontend.relacion') }}</label>
                        {{ equipo.relacion }}
                    </p>
                </div>

                <div class="row m-1 text-center">
                    <div class="col-md-12">
                        <label class="text-uppercase mx-2">{{ $t('frontend.descripcion_rol') }}</label>
                        <p v-if="!editando" class="col-md-12">
                            {{ integrante.descripcion_rol }}
                        </p>
                        <textarea v-else class="form-control col-md-12 text-center" v-model="integrante.descripcion_rol">
                            {{ integrante.descripcion_rol }}
                        </textarea>
                    </div>
                </div>

                <div class="row m-1 text-center">
                    <div class="col-md-12">
                        <label class="text-uppercase mx-2">{{ $t('frontend.meta') }}</label>
                        <p v-if="!editando" class="col-md-12">
                            {{ integrante.meta }}
                        </p>
                            <input v-else class="form-control col-md-12 text-center" v-model="integrante.meta" />
                    </div>
                </div>

                <div class="row m-1 text-center">
                    <div class="col-md-12">
                        <label class="text-uppercase mx-2">{{ $t('frontend.hitos') }}</label>
                        <p v-if="!editando" class="col-md-12">
                            {{ integrante.hitos }}
                        </p>
                        <textarea v-else class="form-control text-center" v-model="integrante.hitos">
                            {{ integrante.hitos }}
                        </textarea>
                    </div>
                </div>
                <div v-if="integrante.archivo_carta_compromiso != null || editando" class="row m-1 text-center">
                    <div class="col-md-12">
                        <label class="text-uppercase mx-2">{{ $t('frontend.archivo_carta_compromiso') }}</label>
                        <div>
                            <a v-if="integrante.archivo_carta_compromiso != null" :href="integrante.archivo_carta_compromiso" target="_blank"> {{ $t('frontend.ver') }}</a>
                            <button  v-if="editando" class="btn btn-light" @click="selectFiles" ><i class="fa fa-edit"></i></button>
                            <input type="file" hidden class="form-control text-center" @change="guardarCartaCompromiso" ref="carta_compromiso">
                            {{ this.nombre_carta_compromiso }}
                        </div>
                        
                    </div>

                    </div>
                </div>
                <div class="row p-1 text-center">
                    <div class="col-md-6">
                        <label class="text-uppercase mx-2">{{ $t('frontend.dia_hora_reunion') }}</label>
                        <p v-if="!editando">
                            {{ integrante.dia_hora_reunion }}
                        </p>
                        <input v-else class="form-control text-center" v-model="integrante.dia_hora_reunion" />
                    </div>

                    <div class="col-md-6">
                        <label class="text-uppercase mx-2">{{ $t('frontend.periodicidad_reunion') }}</label>
                        <p v-if="!editando">
                            {{ integrante.periodicidad_reunion }}
                        </p>
                        <select v-else id="periodicidad_reunion" v-model="equipo.periodicidad_reunion" class="form-control  text-center">
                            <option v-bind:value="$t('frontend.semanal')"> {{ $t('frontend.semanal') }} </option>
                            <option v-bind:value="$t('frontend.quincenal')"> {{ $t('frontend.quincenal') }} </option>
                            <option v-bind:value="$t('frontend.mensual')"> {{ $t('frontend.mensual') }} </option>    
                        </select>
                    </div>
                </div>
                
                <div class="row text-center mt-3">
                    <p class="col-md-6">
                        <label class="text-uppercase mx-2">fecha inicio</label>
                        {{ equipo.fechaInicio }}
                    </p>
                    <p class="col-md-6">
                        <label class="text-uppercase mx-2">fecha fin</label>
                        {{ equipo.fechaFin }}
                    </p>
                </div>
                <div class="row justify-content-center mt-2">
                    <span v-if="editando">
                        <a @click="saveCard" class="btn btn-primary text-white m-2">
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
    name: 'Integrante',
    data: function () {
        var data = {
            editando: this.newCard,
            integrante: [],
            updateCartaCompromiso: false,
            carta_compromiso: '',
            nombre_carta_compromiso: '',
            data: {
                id: this.identifier,
               
            },
        }
        return data;
    },
    props: {
        identifier: {
            type: Number,
            default: null
        },
        equipo: {
            type: Object,
            default: null
        },
    },
    mounted: function () {
        this.formDirty = false;
        this.integrante = this.equipo;
    },
    watch: {

    },
    methods: {
        editCard: function () {
            this.editando = true;
        },
        saveCard: function () {
            this.guardo = false;
            axios.put('/ajax/equipos', this.integrante).then(response => {
                this.submitFiles();
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
            this.$emit('saveCard', this.data);
            this.editando = false;
        },
        deleteCard: function () {
            this.$emit('deleteCard');
        },
        selectFiles: function () {
            this.$refs.carta_compromiso.click();
        },
        submitFiles: function () {
            this.guardo = false;
            const formData = new FormData();
            formData.append('carta_compromiso', this.carta_compromiso);
            formData.append('integrante_id', this.integrante.idIntegrante);
            const headers = { 'Content-Type': 'multipart/form-data' };
            axios.post('/ajax/equipos/carta_compromiso', formData, { headers }).then(response => {
                this.guardo = true;
                this.formDirty = false;
            }).catch((error) => {
            });
        },
        guardarCartaCompromiso: function () {
            this.carta_compromiso = this.$refs.carta_compromiso.files[0];
            this.nombre_carta_compromiso = this.$refs.carta_compromiso.files[0].name;
        },
    },
    computed: {
    }
}
</script>
