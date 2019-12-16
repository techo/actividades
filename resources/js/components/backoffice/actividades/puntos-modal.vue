<template>
    <div :class="{ 'modal':true, 'fade': true, 'in': display }" :style="{ 'display': display ? 'block' : 'none' }" id="inscribir-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="cancelar()" >
                        <span aria-hidden="true">×</span></button>
                    <h4 class="modal-title">Punto</h4>
                </div>
                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.punto }">
                                <label for="punto" >Punto</label>
                                <input v-model="form.punto" name="punto" type="text" class="form-control" required >
                                <span v-if="errors.punto" v-text="errors.punto[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.horario }">
                                <label for="horario" >Horario</label>
                                <input v-model="form.horario" name="horario" type="time" class="form-control" required >
                                <span v-if="errors.horario" v-text="errors.horario[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.idProvincia }">
                                <label for="idProvincia">Provincia</label>
                                <select v-model="form.idProvincia" name="idProvincia" class="form-control" required @change="getLocalidades($event);actividad.idLocalidad=null;" >>
                                    <option v-text="provincia.provincia" v-bind:value="provincia.id" v-for="provincia in provincias" ></option>
                                </select>
                                <span v-if="errors.idProvincia" v-text="errors.idProvincia[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.idLocalidad }">
                                <label for="idLocalidad">Localidad</label>
                                <select v-model="form.idLocalidad" name="idLocalidad" class="form-control" required >
                                    <option v-text="localidad.localidad" v-bind:value="localidad.id" v-for="localidad in localidades" ></option>
                                </select>
                                <span v-if="errors.idLocalidad" v-text="errors.idLocalidad[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div :class="{ 'form-group': true, 'has-error': errors.idPersona }">
                                <label for="idPersona">Persona</label>
                                <v-select
                                    :options="personas" 
                                    @search="onSearch" 
                                    label="nombre" 
                                    v-model="persona" 
                                    :filterable="false"
                                    :selectOnTab="true"
                                ></v-select>
                                <span v-if="errors.idPersona" v-text="errors.idPersona[0]" class="help-block" ></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="estado">Estado</label>
                                <select v-model="form.estado" name="estado" class="form-control" required >
                                    <option value="1" :selected="form.estado == 1 " >Activo</option>
                                    <option value="0" :selected="form.estado == 0 " >Inactivo</option>
                                </select>
                            </div>
                        </div>
                </div>

                </div>
                <div class="modal-footer">
                    <button ref="cancelar" class="btn" @click="cancelar()">Cancelar</button>
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
            form: {
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
        Event.$on('puntos:editar', this.editar);
        this.getActividad();
    },
    watch: { 
        persona(v,vv) {
            this.form.idPersona = v.idPersona
        }
    },
    methods: {
        guardar() {
            if(this.form['idPuntoEncuentro'])
                this.update();
            else
                this.store();
        },
        store(){
            axios.post('/admin/ajax/actividades/' + this.id + '/puntos', this.form)
                .then((datos) => { 
                    Event.$emit('puntos:refrescar'); 
                    this.cancelar(); 
                })
                .catch((error) => {this.errors = this.errors = error.response.data.errors; });
        },
        update () {
            axios.post('/admin/ajax/actividades/' + this.id + '/puntos/' + this.form.idPuntoEncuentro, this.form)
                .then((datos) => { 
                    Event.$emit('puntos:refrescar'); 
                    this.cancelar(); 
                })
                .catch((error) => {this.errors = this.errors = error.response.data.errors; });
        },
        editar(p) {
            this.show();
            axios.get('/admin/ajax/actividades/' + this.id + '/puntos/' + p.id)
                .then((datos) => { 
                    this.form = datos.data; 
                    this.getLocalidades();
                    this.persona = datos.data.persona;
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
            axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias/' + this.form.idProvincia + '/localidades')
                    .then((datos) => { this.localidades = datos.data; }).catch((error) => { debugger; });
        },
        show: function () { this.display = true; },
        hide: function () { this.display = false; },
        reset: function () {
            for (let field in this.form) {
                this.form[field] = null;
            }
            this.reset_errors();
        },
        reset_errors: function () {
            for (let field in this.errors) {
                this.errors[field] = null;
                delete this.errors[field];
            }
        },
        cancelar() {
            this.reset();
            this.reset_errors();
            this.hide();
        },
        onSearch: _.debounce( function (text, loading) {
            if(text.length > 3) {
                loading(true);
                axios.get('/ajax/coordinadores?coordinador=' + text)
                    .then((datos) => { 
                        this.personas = datos.data.data; 
                        loading(false);
                    })
                    .catch((error) => { loading(false); });
                }
            }, 400),

    }
}
</script>