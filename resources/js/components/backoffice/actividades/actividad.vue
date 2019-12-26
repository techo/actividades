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
                            <input name="nombreActividad" type="text" class="form-control" v-model="actividad.nombreActividad"required > </div>
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
                            <select name="idCategoria" @change="getTipos($event.target.value)"  required class="form-control" >
                                <option v-text="categoria.nombre" v-bind:value="categoria.id" v-for="categoria in categorias" :selected="actividad.tipo.idCategoria == categoria.id"></option>
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

                    <div class="col-md-4">
                        <label for="fechaInicio">Empieza</label>
                        <div class="input-group">
                            <input :value="fechaInicio_f" ref="fechaInicio_f" type="date" class="form-control" required style="line-height: inherit;">
                            <span class="input-group-addon">
                                <input :value="fechaInicio_h" ref="fechaInicio_h" type="time" required style="border: none; height: 20px;">
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="fechaFin">Termina</label>
                        <div class="input-group">
                            <input :value="fechaFin_f" ref="fechaFin_f" type="date" class="form-control" required style="line-height: inherit;">
                            <span class="input-group-addon">
                                <input :value="fechaFin_h" ref="fechaFin_h" type="time" required style="border: none; height: 20px;">
                            </span>
                        </div>
                    </div>       
                            
                </div>

                <div class="box" style="border-top: 12px; display: none">

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-4">
                                <label for="fechaInicio">Inscripciones empiezan</label>
                                <div class="input-group">
                                    <input :value="fechaInicioInscripciones_f" ref="" type="date" class="form-control" required style="line-height: inherit;">
                                    <span class="input-group-addon">
                                        <input :value="fechaInicio_h" ref="" type="time" required style="border: none; height: 20px;">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="fechaFin">Terminan</label>
                                <div class="input-group">
                                    <input :value="fechaFinInscripciones_f" ref="" type="date" class="form-control" required style="line-height: inherit;">
                                    <span class="input-group-addon">
                                        <input :value="fechaFin_h" ref="" type="time" required style="border: none; height: 20px;">
                                    </span>
                                </div>
                            </div>       
                                    
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <label for="fechaInicio">Evaluaciones empiezan</label>
                                <div class="input-group">
                                    <input :value="fechaInicioEvaluaciones_f" ref="" type="date" class="form-control" required style="line-height: inherit;">
                                    <span class="input-group-addon">
                                        <input :value="fechaInicioEvaluaciones_h" ref="" type="time" required style="border: none; height: 20px;">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="fechaFin">Terminan</label>
                                <div class="input-group">
                                    <input :value="fechaFinEvaluaciones_f" ref="" type="date" class="form-control" required style="line-height: inherit;">
                                    <span class="input-group-addon">
                                        <input :value="fechaFinEvaluaciones_h" ref="" type="time" required style="border: none; height: 20px;">
                                    </span>
                                </div>
                            </div>       
                                    
                        </div>

                    </div>

                </div>

                <br>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <tinymce-editor 
                                v-model="actividad.descripcion" 
                                :init="{
                                    menubar: 'false',
                                }"
                                toolbar="undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image" 
                                plugins="paste autoresize image preview paste link"
                                
                            ></tinymce-editor>
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
                            <textarea name="mensajeInscripcion" v-model="actividad.mensajeInscripcion" class="form-control" required ></textarea>
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

                    <div class="col-md-1">
                        <div class="form-group">
                            <label for="limiteInscripciones">Cupos</label>
                            <input id="limiteInscripciones" name="limiteInscripciones"
                                   type="number"
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
    import editor from '@tinymce/tinymce-vue'

    import 'tinymce/tinymce'

    // Theme
    import 'tinymce/themes/silver/theme'

    // Plugins
    import 'tinymce/plugins/paste'
    import 'tinymce/plugins/autoresize'
    import 'tinymce/plugins/image'
    import 'tinymce/plugins/preview'
    import 'tinymce/plugins/paste'
    import 'tinymce/plugins/link'

    export default {
        name: "actividad",
        props: ['id'],
        components: { 'tinymce-editor': editor },
        data() {
            return {
                actividad: {
                    nombreActividad: null,
                    descripcion: '',
                    estadoConstruccion: 'Abierta',
                    confirmacion: 0,
                    pago: 0,

                    idTipo: null,
                    idOficina: null,

                    fechaInicio: moment().format('YYYY-MM-DD HH:mm'),
                    fechaFin: moment().add(1, 'days').format('YYYY-MM-DD HH:mm'),

                    lugar: '',
                    idPais: null,
                    idProvincia: null,
                    idLocalidad: null,

                    limiteInscripciones: 0,
                    inscripcionInterna: 0,

                    montoMin: 0,
                    montoMax: 0,
                    moneda: null,
                    fechaLimitePago: null,
                    beca: null,

                    tipo : {
                        idCategoria: 1
                    }
                },
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

            if(this.id) {
                axios.get('/admin/ajax/actividades/' + this.id)
                    .then((datos) => { 
                        this.actividad = datos.data; 
                        this.getTodasRelaciones();
                    }).catch((error) => { debugger; });
            }
            else {
                this.getRelaciones();
            }


        },
        computed: {
            fechaInicio_f(){
                if(this.actividad) 
                    return moment(this.actividad.fechaInicio).format('YYYY-MM-DD');
            },
            fechaInicio_h(){
                if(this.actividad)
                    return moment(this.actividad.fechaInicio).format('HH:mm:ss');
            },
            fechaFin_f(){
                if(this.actividad) 
                    return moment(this.actividad.fechaFin).format('YYYY-MM-DD');
            },
            fechaFin_h(){
                if(this.actividad) 
                    return moment(this.actividad.fechaFin).format('HH:mm:ss');
            },
            fechaInicioInscripciones_f(){
                if(this.actividad) 
                    return moment(this.actividad.fechaInicioInscripciones).format('YYYY-MM-DD');
            },
            fechaInicioInscripciones_h(){
                if(this.actividad)
                    return moment(this.actividad.fechaInicioInscripciones).format('HH:mm:ss');
            },
            fechaFinInscripciones_f(){
                if(this.actividad) 
                    return moment(this.actividad.fechaFinInscripciones).format('YYYY-MM-DD');
            },
            fechaFinInscripciones_h(){
                if(this.actividad)
                    return moment(this.actividad.fechaFinInscripciones).format('HH:mm:ss');
            },
            fechaInicioEvaluaciones_f(){
                if(this.actividad) 
                    return moment(this.actividad.fechaInicioEvaluaciones).format('YYYY-MM-DD');
            },
            fechaInicioEvaluaciones_h(){
                if(this.actividad)
                    return moment(this.actividad.fechaInicioEvaluaciones).format('HH:mm:ss');
            },
            fechaFinEvaluaciones_f(){
                if(this.actividad) 
                    return moment(this.actividad.fechaFinEvaluaciones).format('YYYY-MM-DD');
            },
            fechaFinEvaluaciones_h(){
                if(this.actividad)
                    return moment(this.actividad.fechaFinEvaluaciones).format('HH:mm:ss');
            },
        },
        filters: {},
        watch: {},
        methods: {
            guardar(){
                this.actividad.fechaInicio = moment(this.$refs.fechaInicio_f.value + ' ' + this.$refs.fechaInicio_h.value).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaFin = moment(this.$refs.fechaFin_f.value + ' ' + this.$refs.fechaFin_h.value).format('YYYY-MM-DD HH:mm:ss');
                debugger;

                if(this.id) {
                    axios.post('/admin/ajax/actividades/' + this.id, this.actividad)
                        .then((datos) => { this.actividad = datos.data; }).catch((error) => { debugger; });
                }
                else {
                    axios.post('/admin/actividades/crear', this.actividad)
                        .then((datos) => { window.location = '/admin/actividades/' + datos.data.idActividad; }).catch((error) => { debugger; });
                }
            },
            getRelaciones(){
                this.getPaises();
                this.getOficinas();
                this.getTipos(1);
                this.getCategorias();
            },
            getTodasRelaciones(){
                this.getPaises();
                this.getProvincias();
                this.getLocalidades();
                this.getOficinas();
                this.getTipos(this.actividad.tipo.idCategoria);
                this.getCategorias();
            },
            getPaises(){
                axios.get('/ajax/paises/habilitados')
                    .then((datos) => { this.paises = datos.data; }).catch((error) => { debugger; });
            },
            getProvincias(){
                axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias')
                    .then((datos) => { this.provincias = datos.data; }).catch((error) => { debugger; });
            },
            getLocalidades(){
                axios.get('/ajax/paises/' + this.actividad.idPais + '/provincias/' + this.actividad.idProvincia + '/localidades')
                    .then((datos) => { this.localidades = datos.data; }).catch((error) => { debugger; });
            },
            getOficinas(){
                axios.get('/admin/ajax/oficinas')
                    .then((datos) => { this.oficinas = datos.data; }).catch((error) => { debugger; });
            },
            getTipos(id){
                axios.get('/ajax/categorias/' + id + '/tipos')
                    .then((datos) => { this.tipos = datos.data; }).catch((error) => { debugger; });
            },
            getCategorias(){
                axios.get('/ajax/categorias/')
                    .then((datos) => { this.categorias = datos.data; }).catch((error) => { debugger; });
            },
        }
    }
</script>

<style scoped>

</style>