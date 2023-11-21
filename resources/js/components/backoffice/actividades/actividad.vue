<template>
    <div>
        <div class="box">

            <div class="box-header with-border">
                <h3 class="box-title"></h3>
            </div>

            <div class="box-body">
                
                <div class="row">

                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.nombreActividad }" >
                            <label for="nombreActividad">Nombre</label>
                            <input name="nombreActividad" type="text" class="form-control" v-model="actividad.nombreActividad"required :disabled="!edicion"> 
                            <span class="help-block">{{ errors.nombreActividad }}</span>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.idPais }" >
                            <label for="pais">País</label>
                            <select name="idPais" class="form-control" v-model="actividad.idPais" required 
                            @change="getProvincias($event);getOficinas($event);actividad.idProvincia=null;actividad.idOficina=null; actividad.idLocalidad=null;" 
                            :disabled="!edicion" >
                                <option v-text="pais.nombre" v-bind:value="pais.id" v-for="pais in paises" ></option>
                            </select>
                            <span class="help-block">{{ errors.idPais }}</span>
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
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select name="estadoConstruccion" class="form-control" v-model="actividad.estadoConstruccion" required :disabled="!edicion" >
                                <option value="Abierta" :selected="actividad.estadoConstruccion == 'Abierta'" >Abierta</option>
                                <option value="Cerrada" :selected="actividad.estadoConstruccion == 'Cerrada'" >Cerrada</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
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

                    <div class="col-md-4">
                        <label for="fechaInicio">Empieza</label>
                        <div :class="{ 'input-group': true, 'has-error': errors.fechaInicio }" >
                            <input v-model="fechas.fechaInicio" type="date" @change="fechas.fechaFin=fechas.fechaInicio;" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                            <span class="help-block">{{ errors.fechaInicio }}</span>
                            <span class="input-group-addon">
                                <input v-model="horas.fechaInicio" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="fechaFin">Termina</label>
                        <div :class="{ 'input-group': true, 'has-error': errors.fechaFin }" >
                            <input v-model="fechas.fechaFin" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion">
                            <span class="help-block">{{ errors.fechaFin }}</span>
                            <span class="input-group-addon">
                                <input v-model="horas.fechaFin" type="time" required style="border: none; height: 20px;" :disabled="!edicion">
                            </span>
                        </div>
                    </div>
                            
                </div>

                

                <!-- <ul v-show="fechas.length > 0" style="color: #dd4b39;">
                    <li v-for="(f) in fechas" v-text="f[0] + ': ' + f[1]" ></li>
                </ul> -->

                <div class="row" v-show="edicion">
                    <br>
                    <div class="col-md-12">
                        <label>
                            <input type="checkbox" v-model="calculaFechas" :disabled="!edicion"> Especificar fechas de inscripción/evaluación manualmente
                        </label>
                    </div>
                </div>

                <div class="row" v-show="edicion">
                    <div class="col-md-12" style="clear:both">
                        <p class="help-block">Una actividad necesita un rango de inscripción previo a la actividad y uno de evaluación posterior a la actividad. <br> Si no se especifican se calculan estos rangos 10 días antes y después de la actividad respectivamente.</p>
                    </div>
                </div>

                <div class="box" style="border-top: 12px;" v-show="!edicion || calculaFechas">

                    <div class="box-body">

                        <div class="row">                            
                            <div class="col-md-4 ml-2">
                                <label for="fechaInicioInscripciones">Inscripciones empiezan</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaInicioInscripciones }" >
                                    <input v-model="fechas.fechaInicioInscripciones" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion || !calculaFechas">
                                    <span class="help-block">{{ errors.fechaInicioInscripciones }}</span>
                                    <span class="input-group-addon">
                                        <input v-model="horas.fechaInicioInscripciones" type="time" required style="border: none; height: 20px;" :disabled="!edicion || !calculaFechas">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="fechaFinInscripciones">Terminan</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaFinInscripciones }" >
                                    <input v-model="fechas.fechaFinInscripciones" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion || !calculaFechas">
                                    <span class="help-block">{{ errors.fechaFinInscripciones }}</span>
                                    <span class="input-group-addon">
                                        <input v-model="horas.fechaFinInscripciones" type="time" required style="border: none; height: 20px;" :disabled="!edicion || !calculaFechas">
                                    </span>
                                </div>
                            </div>       
                                    
                        </div>

                        <div class="row">
                            <div class="col-md-4 ml-2">
                                <label for="fechaFin">Evaluaciones empiezan</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaInicioEvaluaciones }" >
                                    <input v-model="fechas.fechaInicioEvaluaciones" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion || !calculaFechas">
                                    <span class="help-block">{{ errors.fechaInicioEvaluaciones }}</span>
                                    <span class="input-group-addon">
                                        <input v-model="horas.fechaInicioEvaluaciones" type="time" required style="border: none; height: 20px;" :disabled="!edicion || !calculaFechas">
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="fechaFin">Terminan</label>
                                <div :class="{ 'input-group': true, 'has-error': errors.fechaFinEvaluaciones }" >
                                    <input v-model="fechas.fechaFinEvaluaciones" type="date" class="form-control" required style="line-height: inherit;" :disabled="!edicion || !calculaFechas">
                                    <span class="help-block">{{ errors.fechaFinEvaluaciones }}</span>
                                    <span class="input-group-addon">
                                        <input v-model="horas.fechaFinEvaluaciones" type="time" required style="border: none; height: 20px;" :disabled="!edicion || !calculaFechas">
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
                <span class="help-block" v-show="virtual==true">En este espacio podes poner tanto el medio donde se realizará (ej, ZOOM, HANHOUTS) o directemente poner la url de la reunión. Tener en cuenta que la misma sería pública en tal caso</span>
                <span class="help-block" v-show="virtual==false">La ubicación es el lugar físico donde se realizará la actividad. Una actividad tiene un solo lugar físico, pero puede tener múltiples puntos de encuentro donde los voluntarios se juntan previo a llegar hasta la ubicación final.</span>
            </div>
            <div class="box-body">

                <div class="row">

                    <div class="col-md-4">
                        <div :class="{ 'form-group': true, 'has-error': errors.lugar }" >
                            <label for="lugar">Lugar / Medio </label>
                            <input id="lugar" name="lugar" type="text" class="form-control" v-model="actividad.lugar" required
                            :disabled="!edicion" >
                            <span class="help-block">{{ errors.lugar }}</span>
                        </div>
                    </div>

                    <div class="col-md-4" v-show="virtual==false">
                        <div :class="{ 'form-group': true, 'has-error': errors.idProvincia }" >
                            <label for="provincia">Provincia</label>
                            <select name="idProvincia" class="form-control" v-model="actividad.idProvincia" required @change="getLocalidades($event)" :disabled="!edicion">
                                <option v-text="provincia.provincia" v-bind:value="provincia.id" v-for="provincia in provincias" ></option>
                            </select>
                            <span class="help-block">{{ errors.idProvincia }}</span>
                        </div>
                    </div>

                    <div class="col-md-4" v-show="virtual==false">
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
           <!--  <div class="box-footer">
                <span class="help-block text-light-blue"><i class="fa  fa-exclamation"></i> El sistema carga automáticamente un punto de encuentro que coincide con la ubicación de la actividad. En caso de ser necesario, se puede editar o borrar y cargar otros puntos de encuentro según la lógica de la actividad.</span>
            </div> -->
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
                            <label for="">Monto</label>
                            <input type="number" class="form-control" v-model="actividad.montoMin" :disabled="!edicion" >
                            <span class="help-block">{{ errors.montoMin }}</span>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="">Monto Max. (Opcional)</label>
                            <input type="number" class="form-control" v-model="actividad.montoMax" :disabled="!edicion">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div :class="{ 'form-group': true, 'has-error': errors.fechaLimitePago }" >
                            <label for="">Fecha límite de pago</label>
                            <input v-model="fechas.fechaLimitePago" type="date" class="form-control" :disabled="!edicion">
                            <span class="help-block">{{ errors.fechaLimitePago }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.linkPago }" >
                            <label for="">Link para el Pago</label>
                            <input type="text" class="form-control" v-model="actividad.linkPago" :disabled="!edicion" >
                            <span class="help-block">{{ errors.linkPago }}</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div :class="{ 'form-group': true, 'has-error': errors.beca }" >
                            <label for="">Link formulario de beca (Opcional) </label>
                            <input type="text" class="form-control" v-model="actividad.beca" :disabled="!edicion" >
                            <span class="help-block">{{ errors.beca }}</span>
                        </div>
                    </div>
                </div>

                <br>

                <div class="row">
                    <div class="col-md-12">
                        <div :class="{ 'form-group': true, 'has-error': errors.mensajeInscripcion }" >
                            <label for="mensajeInscripcion">Mensaje *</label>
                            <p class="help-block">Este mensaje llegar al inscripto al confirmarse su participación.</p>
                            <textarea name="mensajeInscripcion" v-model="actividad.mensajeInscripcion" class="form-control" required :disabled="!edicion" >Has sido Confirmado para la Actividad!</textarea>
                            <span class="help-block">{{ errors.mensajeInscripcion }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Otros</h3>
            </div>
            <div class="box-body">

                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="require_ficha_medica">Requiere Ficha Medica</label>
                            <select name="requiere_ficha_medica" class="form-control" v-model="actividad.requiere_ficha_medica" required :disabled="!edicion">
                                <option value="1" :selected="actividad.requiere_ficha_medica == 1" >Si</option>
                                <option value="0" :selected="actividad.requiere_ficha_medica == 0" >No</option>
                            </select>

                            <p class="help-block">En caso afirmativo la persona será dirigida a cargar su ficha al momento de la inscripción</p>
                        </div>
                    </div>
                    <div v-show="actividad.requiere_ficha_medica == 1" class="col-md-10">
                        <div v-for="(valor, index) in fichaMedicaCampos " class="col-md-4">
                            <div class="row">
                                <label>
                                <input class="col-md-1" :name="valor" v-model="fichaMedicaCampos[index]" type="checkbox" :disabled="!edicion" />
                                <span class="col-md-11">{{ $t('frontend.'+ index) }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="require_ficha_medica">Roles Aplicables</label>
                            <vue-tags-input
                                v-model="tag"
                                :tags="rolesTags"
                                @tags-changed="newTags => rolesTags = newTags"
                            />

                            <p class="help-block">En caso de dar opciones de roles a aplicar, especificar aquí, luego se podra confirmar este rol desde la inscripción</p>
                        </div>
                    </div>
                    <div class="col-md-10">
                        
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="limiteInscripciones">Cupos</label>
                            <input type="number" min="0" class="form-control" v-model="actividad.limiteInscripciones" required
                            :disabled="!edicion" >
                            <p class="help-block">0 es "sin limite de inscriptos"</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="seguimiento_google">Codigo Seguimiento Google</label>
                            <input type="text" id="seguimiento_google" class="form-control" v-model="actividad.seguimiento_google"
                            :disabled="!edicion" >
                            <p class="help-block">En caso de querer hacer un seguimiento particular, este codigo de seguimento será el que se ejecute al momento de ingresar a la inscripcion de la actividad</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="linkEvaluacion">Link de evaluación</label>
                            <input type="text" class="form-control" v-model="actividad.linkEvaluacion" required
                            :disabled="!edicion" >
                            <span class="help-block">{{ errors.linkEvaluacion }}</span>
                            <p class="help-block">Este link llega al momento de mandar evaluaciones (se suma a las ya pedidas por el sistema)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box" v-show="edicion == false">
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
    import editor from '@tinymce/tinymce-vue'
    import vSwitch from 'vue-switches';

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
    import VueTagsInput from '@johmun/vue-tags-input';

    export default {
        name: "actividad",
        props: {'id': {}, 'disabled': {default: false, type: Boolean} },
        components: { 'tinymce-editor': editor, vSwitch, VueTagsInput },
        data() {
            return {
                tag: '',
                rolesTags: [],

                fichaMedicaCampos:{
                    'contacto_emergencia' : false,
                    'grupo_sanguinieo' : false,
                    'cobertura_medica': false,
                    'ficha_alergias' : false,
                    'ficha_alimentacion' : false,
                    'documento_identidad' : false,
                },
                actividad: {
                    nombreActividad: null,
                    descripcion: '',
                    estadoConstruccion: 'Abierta',
                    confirmacion: 0,
                    pago: 0,

                    idTipo: null,
                    idOficina: null,

                    calculaFecha: false,
                    fechaInicio: null,
                    fechaFin: null,

                    lugar: '',
                    idPais: null,
                    idProvincia: null,
                    idLocalidad: null,

                    limiteInscripciones: 0,
                    inscripcionInterna: 0,
                    seguimiento_google: null,

                    requiere_ficha_medica: 0,
                    ficha_medica_campos: {},


                    tipo : {
                        idCategoria: 1
                    }
                },
                fechas: {
                    fechaInicio: null,
                    fechaFin: null,

                    fechaInicioInscripciones: null,
                    fechaFinInscripciones: null,

                    fechaInicioEvaluaciones: null,
                    fechaFinEvaluaciones: null,

                    fechaLimitePago: null,
                },
                horas: {
                    fechaInicio: "19:00:00",
                    fechaFin: "21:00:00",

                    fechaInicioInscripciones: null,
                    fechaFinInscripciones: null,

                    fechaInicioEvaluaciones: null,
                    fechaFinEvaluaciones: null,

                    fechaLimitePago: null,
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
                virtual: false,
            }
        },
        created() {
            this.edicion = !this.disabled;
        },
        mounted() {
            Event.$on('guardar', this.guardar);
            Event.$on('editar', () => { this.edicion = true; });
            Event.$on('clonar', this.clonar);
            Event.$on('eliminar', this.eliminar);

            if(this.id) {
                axios.get('/admin/ajax/actividades/' + this.id)
                    .then((datos) => { 
                        this.actividad = datos.data; 
                        if (this.actividad.ficha_medica_campos)
                            this.fichaMedicaCampos = this.actividad.ficha_medica_campos;
                        else
                            this.fichaMedicaCampos =   {
                                'contacto_emergencia' : false,
                                'grupo_sanguinieo' : false,
                                'cobertura_medica': false,
                                'ficha_alergias' : false,
                                'ficha_alimentacion' : false,
                                'documento_identidad' : false
                            };
                        this.getTodasRelaciones();
                        this.cargarFechas();
                    }).catch((error) => { debugger; });
            }
            else {
                this.getRelaciones();
            }


        },
        computed: {
     
        },
        filters: {},
        watch: {
            fechas: {
                deep: true,
                handler(){
                    this.calcularFechas();
                }
            },
            horas: {
                deep: true,
                handler(){
                    this.calcularFechas();
                }
            },
            calculaFechas: {
                handler(){
                    if (!this.calculaFechas) {
                        this.calcularFechas();
                    }
                }
            },
        },
        methods: {
            cargarFechas(){
                this.calculaFechas = this.actividad.calculaFecha;

                this.fechas.fechaInicio = moment(this.actividad.fechaInicio).format('YYYY-MM-DD');
                this.horas.fechaInicio = moment(this.actividad.fechaInicio).format('HH:mm:ss');
                this.fechas.fechaFin = moment(this.actividad.fechaFin).format('YYYY-MM-DD');
                this.horas.fechaFin = moment(this.actividad.fechaFin).format('HH:mm:ss');

                this.fechas.fechaInicioInscripciones = moment(this.actividad.fechaInicioInscripciones).format('YYYY-MM-DD');
                this.horas.fechaInicioInscripciones = moment(this.actividad.fechaInicioInscripciones).format('HH:mm:ss');

                this.fechas.fechaFinInscripciones = moment(this.actividad.fechaFinInscripciones).format('YYYY-MM-DD');
                this.horas.fechaFinInscripciones = moment(this.actividad.fechaFinInscripciones).format('HH:mm:ss');

                this.fechas.fechaInicioEvaluaciones = moment(this.actividad.fechaInicioEvaluaciones).format('YYYY-MM-DD');
                this.horas.fechaInicioEvaluaciones = moment(this.actividad.fechaInicioEvaluaciones).format('HH:mm:ss');

                this.fechas.fechaFinEvaluaciones = moment(this.actividad.fechaFinEvaluaciones).format('YYYY-MM-DD');
                this.horas.fechaFinEvaluaciones = moment(this.actividad.fechaFinEvaluaciones).format('HH:mm:ss');

                this.fechas.fechaLimitePago = moment(this.actividad.fechaLimitePago).format('YYYY-MM-DD');
                this.horas.fechaLimitePago = moment(this.actividad.fechaLimitePago).format('HH:mm:ss');
            },
            calcularFechas(){
                    if (!this.calculaFechas){
                        if (this.fechas.fechaFinInscripciones != this.fechas.fechaInicio ) {
                            this.fechas.fechaInicioInscripciones = moment(this.fechas.fechaInicio).subtract(10, 'd').format('YYYY-MM-DD');
                            this.fechas.fechaFinInscripciones = this.fechas.fechaInicio;
                        }
                        if (this.fechas.fechaInicioEvaluaciones != moment(this.fechas.fechaFin).add(1,'d').format('YYYY-MM-DD')) {
                            this.fechas.fechaInicioEvaluaciones = moment(this.fechas.fechaFin).add(1,'d').format('YYYY-MM-DD');
                            this.fechas.fechaFinEvaluaciones = moment(this.fechas.fechaFin).add(11, 'd').format('YYYY-MM-DD');
                        }
                        if (this.horas.fechaFinInscripciones != this.horas.fechaInicio ) {
                            this.horas.fechaInicioInscripciones = this.horas.fechaInicio;
                            this.horas.fechaFinInscripciones = this.horas.fechaInicio;
                        }
                        if (this.horas.fechaInicioEvaluaciones != this.horas.fechaFin) {
                            this.horas.fechaInicioEvaluaciones = this.horas.fechaFin;
                            this.horas.fechaFinEvaluaciones = this.horas.fechaFin;
                        }
                    }
            },
            guardar(){
                this.actividad.fechaInicio = moment(this.fechas.fechaInicio + ' ' + this.horas.fechaInicio).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaFin = moment(this.fechas.fechaFin + ' ' + this.horas.fechaFin).format('YYYY-MM-DD HH:mm:ss');
                
                if (this.calculaFechas == 1){
                    this.actividad.calculaFecha = 1;
                }
                else{
                    this.actividad.calculaFecha = 0 ;
                }

                if (this.virtual){
                    this.actividad.idProvincia = 44;
                    this.actividad.idLocalidad = 2663;
                }
                this.actividad.fechaInicioInscripciones = moment(this.fechas.fechaInicioInscripciones + ' ' + this.horas.fechaInicioInscripciones).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaFinInscripciones = moment(this.fechas.fechaFinInscripciones + ' ' + this.horas.fechaFinInscripciones).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaInicioEvaluaciones = moment(this.fechas.fechaInicioEvaluaciones + ' ' + this.horas.fechaInicioEvaluaciones).format('YYYY-MM-DD HH:mm:ss');
                this.actividad.fechaFinEvaluaciones = moment(this.fechas.fechaFinEvaluaciones + ' ' + this.horas.fechaFinEvaluaciones).format('YYYY-MM-DD HH:mm:ss');
                
                if (this.actividad.pago==1){
                    this.actividad.fechaLimitePago = this.fechas.fechaLimitePago;
                }
                
                this.actividad.ficha_medica_campos = this.fichaMedicaCampos;
                this.actividad.roles_tags = this.rolesTags;

                if(this.id) {
                    axios.post('/admin/ajax/actividades/' + this.id, this.actividad)
                        .then((datos) => { 
                            this.actividad = datos.data;
                            Event.$emit('success');
                            this.edicion = false;
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
                axios.get('/ajax/paises/propios')
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
                axios.get('/admin/ajax/oficinas/pais/' + this.actividad.idPais)
                    .then((datos) => { this.oficinas = datos.data; }).catch((error) => { debugger; });
            },
            getTipos(id){
                axios.get('/ajax/categorias/' + id + '/tipos')
                    .then((datos) => { 
                        this.tipos = datos.data; 
                    }).catch((error) => { debugger; });
                if (id == 4) { this.virtual = true; } else { this.virtual = false; }
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
            },
            clonar: function() {
                let url = '/admin/ajax/actividades/'+ this.actividad.idActividad +'/clonar';
                let params = { idActividad: this.actividad.idActividad };
                debugger;
                this.axiosPost(url, function(response, self) {
                    if (response.idActividad) {
                        window.location = '/admin/actividades/' + response.idActividad
                    }
                }, params,
                    function (response, self) {
                    // Si hay error
                        this.errors = error.response.data.errors;
                        Event.$emit('error');
                    })
            },
            eliminar: function () {
                let form = document.getElementById('formDelete');
                form.submit();
            },
        }
    }
</script>

<style scoped>

</style>