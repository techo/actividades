<template>
    <div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Básica</h3>
            </div>
            <div class="box-body">
                
                <div class="row">

                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nombreActividad">Nombre</label>
                            <input id="nombreActividad" name="nombreActividad"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.nombreActividad"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select name="estadoConstruccion" class="form-control" v-model="actividad.estadoConstruccion" required >
                                <option value="Abierta" :selected="actividad.estadoConstruccion == 'Abierta'" >Abierta</option>
                                <option value="Cerrada" :selected="actividad.estadoConstruccion == 'Cerrada'" >Cerrada</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="inscripcionInterna">Visibilidad</label>
                            <select name="inscripcionInterna" class="form-control" v-model="actividad.inscripcionInterna" required >
                                <option value="1" :selected="actividad.inscripcionInterna == 1" >Privada</option>
                                <option value="0" :selected="actividad.inscripcionInterna == 0" >Pública</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="categoria">Categoría</label>
                            <select name="idCategoria" class="form-control" v-model="actividad.idCategoria" required >
                                <option v-text="categoria.nombre" v-bind:value="categoria.idCategoria" v-for="categoria in categorias" ></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select name="idTipo" class="form-control" v-model="actividad.idTipo" required >
                                <option v-text="tipo.nombre" v-bind:value="tipo.idTipo" v-for="tipo in tipos" ></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="oficina">Oficina</label>
                            <select name="idOficina" class="form-control" v-model="actividad.idOficina" required >
                                <option v-text="oficina.nombre" v-bind:value="oficina.id" v-for="oficina in oficinas" ></option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="fechaInicio">Empieza</label>
                            <input id="fechaInicio" name="fechaInicio"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.fechaInicio"
                                   required
                            >
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="fechaFin">Termina</label>
                            <input id="fechaFin" name="fechaFin"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.fechaFin"
                                   required
                            >
                        </div>
                    </div>
                </div>

                <div class="row">
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea name="descripcion" v-model="actividad.descripcion" class="form-control" required ></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Ubicación</h3>
            </div>
            <div class="box-body">

                <div class="row">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="lugar">Lugar</label>
                            <input id="lugar" name="lugar"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.lugar"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pais">País</label>
                            <select name="idPais" class="form-control" v-model="actividad.idPais" required @change="getProvincias($event);actividad.idProvincia=null;actividad.idLocalidad=null;" >
                                <option v-text="pais.nombre" v-bind:value="pais.id" v-for="pais in paises" ></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="provincia">Provincia</label>
                            <select name="idProvincia" class="form-control" v-model="actividad.idProvincia" required @change="getLocalidades($event)">
                                <option v-text="provincia.provincia" v-bind:value="provincia.id" v-for="provincia in provincias" ></option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="localidad">Localidad</label>
                            <select name="idLocalidad" class="form-control" v-model="actividad.idLocalidad" required >
                                <option v-text="localidad.localidad" v-bind:value="localidad.id" v-for="localidad in localidades" ></option>
                            </select>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Confirmación</h3>
            </div>
            <div class="box-body">

                <div class="row">

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="pago">Por pago</label>
                            <select name="pago" class="form-control" v-model="actividad.pago" required >
                                <option value="1" :selected="actividad.pago == 1" >Activado</option>
                                <option value="0" :selected="actividad.pago == 0" >Desactivado</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="confirmacion">Manual</label>
                            <select name="confirmacion" class="form-control" v-model="actividad.confirmacion" required >
                                <option value="1" :selected="actividad.confirmacion == 1" >Activado</option>
                                <option value="0" :selected="actividad.confirmacion == 0" >Desactivado</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="mensajeInscripcion">Mensaje</label>
                            <textarea name="descripcion" v-model="actividad.mensajeInscripcion" class="form-control" required ></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Extras</h3>
            </div>
            <div class="box-body">

                <div class="row">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="limiteInscripciones">Cupos</label>
                            <input id="limiteInscripciones" name="limiteInscripciones"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.limiteInscripciones"
                                   required
                            >
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</template>

<script>
    import store from '../stores/store'; //legado, sacar en cuanto sea posible

    export default {
        name: "actividad",
        props: ['id'],
        components: {},
        data() {
            return {
                actividad: {},
                paises: [],
                provincias: [],
                localidades: [],
                oficinas: [],
                tipos: [],
                categorias: [],
            }
        },
        created() {
            store.commit('initIdActividad', this.id); // legado, sacar en cuanto se pueda
        },
        mounted() {
            Event.$on('guardar', this.guardar);

            axios.get('/admin/ajax/actividades/' + this.id)
                .then((datos) => { 
                    this.actividad = datos.data; 
                    this.getRelaciones();
                })
                .catch((error) => { debugger; });

        },
        computed: {},
        filters: {},
        watch: {},
        methods: {
            guardar(){
                axios.post('/admin/ajax/actividades/' + this.id, this.actividad)
                    .then((datos) => { this.actividad = datos.data; })
                    .catch((error) => { debugger; });
            },
            getRelaciones(){
                this.getPaises();
                this.getProvincias();
                this.getLocalidades();
                this.getOficinas();
                this.getTipos();
                this.getCategorias();
            },
            getPaises(){
                axios.get('/ajax/paises/habilitados')
                    .then((datos) => { this.paises = datos.data; })
                    .catch((error) => { debugger; });
            },
            getProvincias(){
                axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias')
                    .then((datos) => { this.provincias = datos.data; })
                    .catch((error) => { debugger; });
            },
            getLocalidades(){
                axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias/' + this.actividad.idProvincia + '/localidades')
                    .then((datos) => { this.localidades = datos.data; })
                    .catch((error) => { debugger; });
            },
            getOficinas(){
                axios.get('/admin/ajax/oficinas')
                    .then((datos) => { this.oficinas = datos.data; })
                    .catch((error) => { debugger; });
            },
            getTipos(){
                axios.get('/ajax/categorias/1/tipos')
                    .then((datos) => { this.tipos = datos.data; })
                    .catch((error) => { debugger; });
            },
            getCategorias(){
                axios.get('/ajax/categorias/')
                    .then((datos) => { this.categorias = datos.data; })
                    .catch((error) => { debugger; });
            },
        }
    }
</script>

<style scoped>

</style>