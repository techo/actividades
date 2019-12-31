<template>
    <div>
        <div class="box">

            <div class="box-header with-border">
                <h3 class="box-title">Básica</h3>
            </div>

            <div class="box-body">
                
                <div class="row">

                    <div class="col-md-8">
                        <div :class="{ 'form-group': true, 'has-error': errors.nombreActividad }" >
                            <label for="nombreActividad">Nombre</label>
                            <input name="nombreActividad" type="text" class="form-control" v-model="actividad.nombreActividad"required :disabled="!edicion"> 
                            <span class="help-block">{{ errors.nombreActividad }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select name="estadoConstruccion" class="form-control" v-model="actividad.estadoConstruccion" required :disabled="!edicion" >
                                <option value="Abierta" :selected="actividad.estadoConstruccion == 'Abierta'" >Abierta</option>
                                <option value="Cerrada" :selected="actividad.estadoConstruccion == 'Cerrada'" >Cerrada</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="inscripcionInterna">Visibilidad</label>
                            <select name="inscripcionInterna" class="form-control" v-model="actividad.inscripcionInterna" required :disabled="!edicion" >
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
                            <select name="idCategoria" @change="getTipos($event.target.value)"  required class="form-control" :disabled="!edicion" >
                                <option v-bind:value="categoria.id" v-for="categoria in categorias" :selected="actividad.tipo.idCategoria == categoria.id">{{ $t('frontend.' + categoria.nombre) }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.idTipo }" >
                            <label for="tipo">Tipo</label>
                            <select name="idTipo" class="form-control" v-model="actividad.idTipo" required :disabled="!edicion" >
                                <option v-text="tipo.nombre" v-bind:value="tipo.idTipo" v-for="tipo in tipos" ></option>
                            </select>
                            <span class="help-block">{{ errors.idTipo }}</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.idOficina }" >
                            <label for="oficina">Oficina</label>
                            <select name="idOficina" class="form-control" v-model="actividad.idOficina" required :disabled="!edicion">
                                <option v-text="oficina.nombre" v-bind:value="oficina.id" v-for="oficina in oficinas" ></option>
                            </select>
                            <span class="help-block">{{ errors.idOficina }}</span>
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4">
                        <label for="fechaInicio">Empieza</label>
                        <div :class="{ 'input-group': true, 'has-error': errors.fechaInicio }" >
                            <input :value="fechaInicio_f" ref="fechaInicio_f" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                            <span class="input-group-addon">
                                <input :value="fechaInicio_h" ref="fechaInicio_h" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="fechaFin">Termina</label>
                        <div :class="{ 'input-group': true, 'has-error': errors.fechaFin }" >
                            <input :value="fechaFin_f" ref="fechaFin_f" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                            <span class="input-group-addon">
                                <input :value="fechaFin_h" ref="fechaFin_h" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                            </span>
                        </div>
                    </div>
                            
                </div>

                <div class="row">
                    <div class="col-md-12" style="clear:both">
                        <p class="help-block">Una actividad necesita un rango de inscripción previo a la actividad y uno de evaluación posterior a la actividad. Si no se especifican se calculan estos rangos 10 días antes y después de la actividad respectivamente.</p>
                    </div>
                </div>

                <ul v-show="fechas.length > 0" style="color: #dd4b39;">
                    <li v-for="(f) in fechas" v-text="f[0] + ': ' + f[1]" ></li>
                </ul>

                <div class="row">
                    <div class="col-md-12">
                        <input type="checkbox" v-model="calculaFechas" > Especificar fechas de inscripción/evaluación manualmente
                    </div>
                </div>

                <div class="box" style="border-top: 12px;" v-show="calculaFechas">

                    <div class="box-body">

                        <div class="row">

                            <div class="col-md-4">
                                <label for="fechaInicio">Inscripciones empiezan</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaInicioInscripciones }" >
                                    <input :value="fechaInicioInscripciones_f" ref="fechaInicioInscripciones_f" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                                    <span class="input-group-addon">
                                        <input :value="fechaInicioInscripciones_h" ref="fechaInicioInscripciones_h" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="fechaFin">Terminan</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaFinInscripciones }" >
                                    <input :value="fechaFinInscripciones_f" ref="fechaFinInscripciones_f" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                                    <span class="input-group-addon">
                                        <input :value="fechaFinInscripciones_h" ref="fechaFinInscripciones_h" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                                    </span>
                                </div>
                            </div>       
                                    
                        </div>

                        <div class="row">

                            <div class="col-md-4">
                                <label for="fechaInicio">Evaluaciones empiezan</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaInicioEvaluaciones }" >
                                    <input :value="fechaInicioEvaluaciones_f" ref="fechaInicioEvaluaciones_f" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                                    <span class="input-group-addon">
                                        <input :value="fechaInicioEvaluaciones_h" ref="fechaInicioEvaluaciones_h" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="fechaFin">Terminan</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaFinEvaluaciones }" >
                                    <input :value="fechaFinEvaluaciones_f" ref="fechaFinEvaluaciones_f" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                                    <span class="input-group-addon">
                                        <input :value="fechaFinEvaluaciones_h" ref="fechaFinEvaluaciones_h" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                                    </span>
                                </div>
                            </div>       
                                    
                        </div>

                    </div>

                </div>

                <br>

                <div class="row">
                    <div class="col-md-12">
                        <div :class="{ 'form-group': true, 'has-error': errors.descripcion }" >
                            <label for="descripcion">Descripción</label>
                            <tinymce-editor 
                                v-model="actividad.descripcion" 
                                :init="{
                                    menubar: 'false',
                                    file_picker_callback: tiny_mce_filemanager_callback,
                                    relative_urls: false,
                                    resize: true,
                                }"
                                toolbar="undo redo | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image" 
                                plugins="paste autoresize image preview paste link"
                                :disabled="!edicion"
                            ></tinymce-editor>
                            <span class="help-block">{{ errors.descripcion }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Ubicación</h3>
                <span class="help-block">La ubicación es el lugar físico donde se va a realizar la actividad. Una actividad tiene un solo lugar físico, pero puede tener múltiples puntos de encuentro donde los voluntarios se juntan previo a llegar hasta la ubicación final.</span>
            </div>
            <div class="box-body">

                <div class="row">

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.lugar }" >
                            <label for="lugar">Lugar</label>
                            <input id="lugar" name="lugar" type="text" class="form-control" v-model="actividad.lugar" required
                            :disabled="!edicion" >
                            <span class="help-block">{{ errors.lugar }}</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.idPais }" >
                            <label for="pais">País</label>
                            <select name="idPais" class="form-control" v-model="actividad.idPais" required @change="getProvincias($event);actividad.idProvincia=null;actividad.idLocalidad=null;" :disabled="!edicion" >
                                <option v-text="pais.nombre" v-bind:value="pais.id" v-for="pais in paises" ></option>
                            </select>
                            <span class="help-block">{{ errors.idPais }}</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.idProvincia }" >
                            <label for="provincia">Provincia</label>
                            <select name="idProvincia" class="form-control" v-model="actividad.idProvincia" required @change="getLocalidades($event)" :disabled="!edicion">
                                <option v-text="provincia.provincia" v-bind:value="provincia.id" v-for="provincia in provincias" ></option>
                            </select>
                            <span class="help-block">{{ errors.idProvincia }}</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.idLocalidad }" >
                            <label for="localidad">Localidad</label>
                            <select name="idLocalidad" class="form-control" v-model="actividad.idLocalidad" required :disabled="!edicion">
                                <option v-text="localidad.localidad" v-bind:value="localidad.id" v-for="localidad in localidades" ></option>
                            </select>
                            <span class="help-block">{{ errors.idLocalidad }}</span>
                        </div>
                    </div>

                </div>

            </div>
            <div class="box-footer">
                <span class="help-block text-light-blue"><i class="fa  fa-exclamation"></i> El sistema carga automáticamente un punto de encuentro que coincide con la ubicación de la actividad. En caso de ser necesario, se puede editar o borrar y cargar otros puntos de encuentro según la lógica de la actividad.</span>
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Confirmación</h3>
                <p class="help-block">Una actividad puede o no requerir que sus inscriptos confirmen su participación. Hay cuatro opciones:
                    <ul>
                        <li><b>Automática</b>: al inscribirse están automáticamente confirmados</li>
                        <li><b>Por donación</b>: se pre-inscriben y tienen que realizar una donación para estar confirmados</li>
                        <li><b>Manual</b>: se pre-inscriben y los tiene que confirmar manualmente un coordinador de actividad</li>
                        <li><b>Manual y por donación</b>: combina las dos anteriores. Se pre-inscribe, tiene que confirmarlo un coordinador y recién ahí confirmar con una donación.</li>
                    </ul>
                </p>
            </div>

            <div class="box-body">

                <div class="row">

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="pago">Por pago</label>
                            <select name="pago" class="form-control" v-model="actividad.pago" required :disabled="!edicion">
                                <option value="1" :selected="actividad.pago == 1" >Activado</option>
                                <option value="0" :selected="actividad.pago == 0" >Desactivado</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="confirmacion">Manual</label>
                            <select name="confirmacion" class="form-control" v-model="actividad.confirmacion" required :disabled="!edicion">
                                <option value="1" :selected="actividad.confirmacion == 1" >Activado</option>
                                <option value="0" :selected="actividad.confirmacion == 0" >Desactivado</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="row" v-show="actividad.pago == 1">

                    <div class="col-md-2">
                        <div :class="{ 'form-group': true, 'has-error': errors.montoMin }" >
                            <label for="fechaInicio">Monto Min.</label>
                            <input type="number" class="form-control" v-model="actividad.montoMin" :disabled="!edicion" >
                            <span class="help-block">{{ errors.montoMin }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="fechaInicio">Monto Max.</label>
                            <input type="number" class="form-control" v-model="actividad.montoMax" :disabled="!edicion">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.fechaLimitePago }" >
                            <label for="fechaInicio">Fecha límite de pago</label>
                            <input ref="fechaLimitePago_f" type="date" class="form-control" :value="fechaLimitePago_f" :disabled="!edicion">
                            <span class="help-block">{{ errors.fechaLimitePago }}</span>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div :class="{ 'form-group': true, 'has-error': errors.beca }" >
                            <label for="fechaInicio">Link formulario de beca</label>
                            <input type="text" class="form-control" v-model="actividad.beca" :disabled="!edicion" >
                            <span class="help-block">{{ errors.beca }}</span>
                        </div>
                    </div>

                </div>

                <br>

                <div class="row">
                    <div class="col-md-12">
                        <div :class="{ 'form-group': true, 'has-error': errors.mensajeInscripcion }" >
                            <label for="mensajeInscripcion">Mensaje</label>
                            <p class="help-block">Este mensaje llegar al inscripto al confirmarse su participación.</p>
                            <textarea name="mensajeInscripcion" v-model="actividad.mensajeInscripcion" class="form-control" required :disabled="!edicion" ></textarea>
                            <span class="help-block">{{ errors.mensajeInscripcion }}</span>
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
                            <input type="number" class="form-control" v-model="actividad.limiteInscripciones" required
                            :disabled="!edicion" >
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Auditoría</h3>
            </div>
            <div class="box-body">
                <p class="text-muted">
                    Última modificación: {{ actividad.fechaModificacion }}&nbsp;<a class="btn btn-primary btn-sm" @click="cargarAuditoria(actividad.idActividad)">Ver auditoría</a>
                </p>
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
        props: {'id': {}, 'disabled': {default: false, type: Boolean} },
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

                    fechaInicio: moment().add(1, 'days').format('YYYY-MM-DD 09:00:00'),
                    fechaFin: moment().add(1, 'days').format('YYYY-MM-DD 18:00:00'),

                    lugar: '',
                    idPais: null,
                    idProvincia: null,
                    idLocalidad: null,

                    limiteInscripciones: 0,
                    inscripcionInterna: 0,

                    tipo : {
                        idCategoria: 1
                    }
                },
                errors: {},
                paises: [],
                provincias: [],
                localidades: [],
                oficinas: [],
                tipos: [],
                categorias: [],
                calculaFechas: false,
                edicion: false,
            }
        },
        created() {
            store.commit('initIdActividad', this.id); // legado, sacar en cuanto se pueda
            this.edicion = !this.disabled;
        },
        mounted() {
            Event.$on('guardar', this.guardar);
            Event.$on('editar', () => { this.edicion = true; });

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
                if(this.actividad.fechaInicioInscripciones) 
                    return moment(this.actividad.fechaInicioInscripciones).format('YYYY-MM-DD');
            },
            fechaInicioInscripciones_h(){
                if(this.actividad.fechaInicioInscripciones)
                    return moment(this.actividad.fechaInicioInscripciones).format('HH:mm:ss');
            },
            fechaFinInscripciones_f(){
                if(this.actividad.fechaFinInscripciones) 
                    return moment(this.actividad.fechaFinInscripciones).format('YYYY-MM-DD');
            },
            fechaFinInscripciones_h(){
                if(this.actividad.fechaFinInscripciones)
                    return moment(this.actividad.fechaFinInscripciones).format('HH:mm:ss');
            },
            fechaInicioEvaluaciones_f(){
                if(this.actividad.fechaInicioEvaluaciones) 
                    return moment(this.actividad.fechaInicioEvaluaciones).format('YYYY-MM-DD');
            },
            fechaInicioEvaluaciones_h(){
                if(this.actividad.fechaInicioEvaluaciones)
                    return moment(this.actividad.fechaInicioEvaluaciones).format('HH:mm:ss');
            },
            fechaFinEvaluaciones_f(){
                if(this.actividad.fechaFinEvaluaciones) 
                    return moment(this.actividad.fechaFinEvaluaciones).format('YYYY-MM-DD');
            },
            fechaFinEvaluaciones_h(){
                if(this.actividad.fechaFinEvaluaciones)
                    return moment(this.actividad.fechaFinEvaluaciones).format('HH:mm:ss');
            },
            fechaLimitePago_f(){
                if(this.actividad.fechaLimitePago)
                    return moment(this.actividad.fechaLimitePago).format('YYYY-MM-DD');
            },
            fechas() {
                return Object.keys(this.errors).filter((v) => { return v.match('fecha') }).map((v) => { return [v, this.errors[v]] });
            }
        },
        filters: {},
        watch: {},
        methods: {
            guardar(){
                this.actividad.fechaInicio = moment(this.$refs.fechaInicio_f.value + ' ' + this.$refs.fechaInicio_h.value).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaFin = moment(this.$refs.fechaFin_f.value + ' ' + this.$refs.fechaFin_h.value).format('YYYY-MM-DD HH:mm:ss');
                debugger;
                if(this.$refs.fechaLimitePago_f.value != "")
                    this.actividad.fechaLimitePago = moment(this.$refs.fechaLimitePago_f.value + ' 23:59:00').format('YYYY-MM-DD HH:mm:ss');
                else
                    this.actividad.fechaLimitePago = null;

                if(this.calculaFechas){
                    this.actividad.fechaInicioInscripciones = moment(this.$refs.fechaInicioInscripciones_f.value + ' ' + this.$refs.fechaInicioInscripciones_h.value).format('YYYY-MM-DD HH:mm:ss');
                    this.actividad.fechaFinInscripciones = moment(this.$refs.fechaFinInscripciones_f.value + ' ' + this.$refs.fechaFinInscripciones_h.value).format('YYYY-MM-DD HH:mm:ss');

                    this.actividad.fechaInicioEvaluaciones = moment(this.$refs.fechaInicioEvaluaciones_f.value + ' ' + this.$refs.fechaInicioEvaluaciones_h.value).format('YYYY-MM-DD HH:mm:ss');
                    this.actividad.fechaFinEvaluaciones = moment(this.$refs.fechaFinEvaluaciones_f.value + ' ' + this.$refs.fechaFinEvaluaciones_h.value).format('YYYY-MM-DD HH:mm:ss');
                }

                if(this.id) {
                    axios.post('/admin/ajax/actividades/' + this.id, this.actividad)
                        .then((datos) => { 
                            this.actividad = datos.data;
                            Event.$emit('success');
                            this.reset_errors();

                        })
                        .catch((error) => { 
                            this.errors = error.response.data.errors;
                            Event.$emit('error');
                        });
                }
                else {
                    this.errors = {};
                    axios.post('/admin/actividades/crear', this.actividad)
                        .then((datos) => { window.location = '/admin/actividades/' + datos.data.idActividad; })
                        .catch((error) => {
                            this.errors = error.response.data.errors;
                            Event.$emit('error');
                        });
                }
            },
            reset_errors: function () {
                for (let field in this.errors) {
                    this.errors[field] = null;
                    delete this.errors[field];
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
            tiny_mce_filemanager_callback(callback, value, meta) {
                //gracias a esto ❤ https://github.com/UniSharp/laravel-filemanager/issues/759 
                let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                let y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;
                let cmsURL = '/laravel-filemanager?editor=tinymce5&field_name=' + value;
                if (meta.filetype == 'image') { cmsURL = cmsURL + "&type=Images"; } 
                else { cmsURL = cmsURL + "&type=Files"; }

                tinyMCE.activeEditor.windowManager.openUrl({
                    url : cmsURL,
                    title : 'Administrador de archivos',
                    width : x * 0.8,
                    height : y * 0.8,
                    resizable : "yes",
                    close_previous : "no",
                    onMessage: (api, message) => {
                        callback(message.content);
                    }
                });
            },
            cargarAuditoria: function(id) {
                Event.$emit('cargarAuditoria', {tabla: 'actividad', id: id});
            }
        }
    }
</script>

<style scoped>

</style>