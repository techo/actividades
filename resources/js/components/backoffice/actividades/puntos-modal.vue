<template>
    <div :class="{ 'modal':true, 'fade': true, 'in': display }" :style="{ 'display': display ? 'block' : 'none' }" id="inscribir-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="display = false" >
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Puntos</h4>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="punto" >Punto</label>
                                <input v-model="punto.punto" name="punto" type="text" class="form-control" required >
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="horario" >Horario</label>
                                <input v-model="punto.horario" name="horario" type="time" class="form-control" required >
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="idProvincia">Provincia</label>
                                <select v-model="punto.idProvincia" name="idProvincia" class="form-control" required @change="getLocalidades($event);actividad.idLocalidad=null;" >>
                                    <option v-text="provincia.provincia" v-bind:value="provincia.id" v-for="provincia in provincias" ></option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="idLocalidad">Localidad</label>
                                <select v-model="punto.idLocalidad" name="idLocalidad" class="form-control" required >
                                    <option v-text="localidad.localidad" v-bind:value="localidad.id" v-for="localidad in localidades" ></option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="idPersona">Persona</label>
                                <v-select
                                    :options="personas" 
                                    @search="onSearch" 
                                    label="nombre" 
                                    v-model="persona" 
                                    :filterable="false"
                                    :selectOnTab="true"
                                ></v-select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select v-model="punto.estado" name="estado" class="form-control" required >
                                    <option value="1" :selected="punto.estado == 1 " >Activo</option>
                                    <option value="0" :selected="punto.estado == 0 " >Inactivo</option>
                                </select>
                            </div>
                        </div>
                </div>

                </div>
                <div class="modal-footer">
                    <button ref="cancelar" class="btn" >Cancelar</button>
                    <button ref="guardar" class="btn btn-primary" @click.prevent="guardar()" >Guardar</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

import vSelect from 'vue-select';
import { debounce } from 'lodash';

export default {
    components: { 'v-select': vSelect },
    props: [ 'id' ],
    data: function () {
        return {
            display: false,
            actividad: {},
            persona: null,
            personas: [],
            provincias: [],
            localidades: [],
            punto: {
                punto: null,
                horario: null,
                idProvincia: null,
                idLocalidad: null,
                idPersona: null,
                estado: 1,
            },
            errors: {}
        }
    },
    mounted() {
        Event.$on('puntos:crear', this.show);
        this.getActividad();
    },
    watch: { 
        persona(v,vv) {
            this.punto.idPersona = v.idPersona
        }
    },
    methods: {
        guardar(){
            axios.post('/admin/ajax/actividades/' + this.id + '/puntos', this.punto)
                .then((datos) => {
                    //refrescar tabla
                }).catch((error) => { debugger; });
        },
        getActividad() {
            axios.get('/admin/ajax/actividades/' + this.id )
                .then((datos) => { 
                    this.actividad = datos.data;
                    this.getProvincias();
                }).catch((error) => { debugger; });
        },
        getProvincias(){
            axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias')
                .then((datos) => { this.provincias = datos.data; }).catch((error) => { debugger; });
        },
        getLocalidades(){
            axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias/' + this.punto.idProvincia + '/localidades')
                    .then((datos) => { this.localidades = datos.data; }).catch((error) => { debugger; });
        },
        show: function () { this.display = true; },
        hide: function () { this.display = false; },
        onSearch: _.debounce( function (text, loading) {
            if(text.length > 3) {
                loading(true);
                axios.get('/ajax/coordinadores?coordinador=' + text)
                    .then((datos) => { 
                        this.personas = datos.data.data; 
                        loading(false);
                    })
                    .catch((error) => { 
                        console.log(error);
                        loading(false);
                    });
                }
            }, 400),

    }
}
</script>