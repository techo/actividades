<template>
    <div class="ficha-comunidad-form">
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('backend.general_information') }}</h3>
            </div>
            <div class="box-body">
                
                <div class="row">   
                    <!-- Cantidad de familias -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>{{ $t('comunidad_ficha_inicial.cantidad_familias') }}</span>
                            <input type="number" class="form-control" v-model="data.cantidad_familias" :disabled="readonly">
                        </div>
                    </div>
    
                    <!-- Cantidad de viviendas -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>{{ $t('comunidad_ficha_inicial.cantidad_viviendas') }}</span>
                            <input type="number" class="form-control" v-model="data.cantidad_viviendas" :disabled="readonly">
                        </div>
                    </div>
    
                    <!-- Fecha de formación -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>{{ $t('comunidad_ficha_inicial.fecha_formacion') }}</span>
                            <input type="date" class="form-control" v-model="data.fecha_formacion" :disabled="readonly">
                        </div>
                    </div>
    
                    <!-- Forma de constitución -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>{{ $t('comunidad_ficha_inicial.forma_constitucion') }}</span>
                            <select class="form-control" v-model="data.forma_constitucion" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesFormaConstitucion" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Georeferencia (texto) -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>{{ $t('comunidad_ficha_inicial.georeferencia') }}</span>
                            <input type="text" class="form-control" v-model="data.georeferencia" :disabled="readonly">
                        </div>
                    </div>

                    <!-- Año de inicio intervención TECHO -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>{{ $t('comunidad_ficha_inicial.anio_inicio_techo') }}</span>
                            <input type="number" class="form-control" v-model="data.anio_inicio_techo" :disabled="readonly">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('comunidad_ficha_inicial.legalidad_y_riesgos') }}</h3>
            </div>
            <div class="box-body">
                <!-- Propietario actual del terreno -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.propietario_actual') }}</span>
                        <select class="form-control" v-model="data.propietario_actual" :disabled="readonly">
                            <option disabled value="">Seleccione</option>
                            <option v-for="opcion in opcionesPropietario" :key="opcion.text" :value="opcion.text">
                                {{ $t('comunidad_ficha_inicial.'+opcion.text) }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Estado de legalización -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.estado_legalizacion') }}</span>
                        <select class="form-control" v-model="data.estado_legalizacion" :disabled="readonly">
                            <option disabled value="">Seleccione</option>
                            <option v-for="opcion in opcionesLegalizacion" :key="opcion.text" :value="opcion.text">
                                {{ $t('comunidad_ficha_inicial.'+opcion.text) }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Riesgo a eventos naturales -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.riesgo_eventos') }}</span>
                        <select class="form-control" v-model="data.riesgo_eventos" :disabled="readonly">
                            <option disabled value="">Seleccione</option>
                            <option v-for="opcion in opcionesRiesgo" :key="opcion.text" :value="opcion.text">
                                {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                            
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Riesgo a desalojos -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.riesgo_desalojo') }}</span>
                        <select class="form-control" v-model="data.riesgo_desalojo" :disabled="readonly">
                            <option disabled value="">Seleccione</option>
                            <option v-for="opcion in opcionesRiesgo" :key="opcion.text" :value="opcion.text">
                                {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                            
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Riesgos naturales (tags) -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.riesgos_naturales') }}</span>
                        <vue-tags-input
                            v-model="tagRiesgoNatural"
                            :tags="data.riesgos_naturales"
                            :autocomplete-items="filteredRiesgosNaturales" 
                            add-only-from-autocomplete
                            :disabled="readonly"
                            @tags-changed="newTags => data.riesgos_naturales = newTags"
                        />
                    </div>
                </div>
                <!-- Factores antrópicos cercanos -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>Factores antrópicos cercanos (menos de 500 m)</span>
                        <vue-tags-input
                            v-model="tagFactorAntropico"
                            :tags="data.factores_antropicos"
                            :autocomplete-items="filteredFactoresAntropicos"
                            add-only-from-autocomplete
                            :disabled="readonly"
                            @tags-changed="newTags => data.factores_antropicos = newTags"
                        />
                    </div>
                </div>
            </div>
        </div>
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('comunidad_ficha_inicial.infraestructura_y_servicios') }}</h3>
            </div>
            <div class="box-body">
                    <!-- Material de calles principales -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Material de calles principales</span>
                            <select class="form-control" v-model="data.material_calles" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesMaterialCalles" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                                
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Acceso a electricidad -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Acceso a electricidad</span>
                            <select class="form-control" v-model="data.acceso_electricidad" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesElectricidad" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                                
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Acceso a agua potable -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Acceso a agua potable</span>
                            <select class="form-control" v-model="data.acceso_agua" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesAguaPotable" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                                
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Manejo de aguas residuales -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Manejo de aguas residuales</span>
                            <select class="form-control" v-model="data.manejo_aguas_residuales" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesAguasResiduos" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                                
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Manejo de aguas pluviales -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Manejo de aguas pluviales</span>
                            <select class="form-control" v-model="data.manejo_aguas_pluviales" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesAguasPluviales" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                                
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Material de pisos -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Material predominante en pisos</span>
                            <select class="form-control" v-model="data.material_pisos" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesPisos" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                                
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Material de paredes -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Material predominante en paredes</span>
                            <select class="form-control" v-model="data.material_paredes" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesParedes" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                                
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Material de techos -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>Material predominante en techos</span>
                            <select class="form-control" v-model="data.material_techos" :disabled="readonly">
                                <option disabled value="">Seleccione</option>
                                <option v-for="opcion in opcionesTechos" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}

                                
                                </option>
                            </select>
                        </div>
                    </div>
                    <!-- Alumbrado público -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>{{ $t('comunidad_ficha_inicial.alumbrado_publico') }}</span>
                            <select class="form-control" v-model="data.alumbrado_publico" :disabled="readonly">
                            <option disabled value="">Seleccione</option>
                            <option v-for="opcion in opcionesAlumbrado" :key="opcion.text" :value="opcion.text">
                                        {{ $t('comunidad_ficha_inicial.'+opcion.text) }}
                                    </option>
                            </select>
                        </div>
                    </div>

                    <!-- Equipamientos presentes -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <span>{{ $t('comunidad_ficha_inicial.equipamientos') }}</span>
                            <vue-tags-input
                            v-model="tagEquipamiento"
                            :tags="data.equipamientos_presentes"
                            :autocomplete-items="filteredEquipamientos"
                            add-only-from-autocomplete
                            :disabled="readonly"
                            @tags-changed="newTags => data.equipamientos_presentes = newTags"
                            />
                        </div>
                    </div>
            </div>
        </div>
        <div class="box">
            <div class="box-header with-border bg-primary">
                <h3 class="box-title bg-primary">{{ $t('comunidad_ficha_inicial.organizacion_comunitaria') }}</h3>
            </div>
            <div class="box-body">
                    
                <!-- Organización comunitaria -->
                <!-- <div class="col-md-12">
                <div class="form-group">
                    <span>{{ $t('comunidad_ficha_inicial.tiene_organizacion') }}</span>
                    <select class="form-control" v-model="data.tiene_organizacion" :disabled="readonly">
                    <option :value="true">{{ $t('backend.yes') }}</option>
                    <option :value="false">{{ $t('backend.no') }}</option>
                    </select>
                </div>
                </div>-->

                <!-- Liderazgos electos -->
                <!-- <div class="col-md-12">
                <div class="form-group">
                    <span>{{ $t('comunidad_ficha_inicial.liderazgos_electos') }}</span>
                    <select class="form-control" v-model="data.liderazgos_democraticos" :disabled="readonly">
                    <option :value="true">{{ $t('backend.yes') }}</option>
                    <option :value="false">{{ $t('backend.no') }}</option>
                    </select>
                </div>
                </div> -->

                <!-- Año de elección -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.anio_eleccion') }}</span>
                        <input type="date" class="form-control" v-model="data.anio_eleccion_organizacion" :disabled="readonly">
                    </div>
                </div>

                <!-- Frecuencia de reunión -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.periodicidad_reunion') }}</span>
                        <select class="form-control" v-model="data.frecuencia_reunion_organizacion" :disabled="readonly">
                        <option disabled value="">Seleccione</option>
                        <option v-for="opcion in opcionesFrecuenciaReunion" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}
                                </option>
                        </select>
                    </div>
                </div>

                <!-- Actividades de la organización -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.actividades_organizacion') }}</span>
                        <input type="text" class="form-control" v-model="data.actividades_organizacion" :disabled="readonly">
                    </div>
                </div>

                <!-- Otros grupos comunitarios -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.otros_grupos') }}</span>
                        <select class="form-control" v-model="data.otro_grupo_comunitario" :disabled="readonly">
                        <option :value="true">{{ $t('backend.yes') }}</option>
                        <option :value="false">{{ $t('backend.no') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Tipo de grupo comunitario -->
                <div v-show="data.otro_grupo_comunitario" class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.tipo_grupo') }}</span>
                        <select class="form-control" v-model="data.tipo_otro_grupo" :disabled="readonly">
                        <option disabled value="">Seleccione</option>
                        <option v-for="opcion in opcionesTipoGrupo" :key="opcion.text" :value="opcion.text">
                                    {{ $t('comunidad_ficha_inicial.'+opcion.text) }}
                                </option>
                        </select>
                    </div>
                </div>

                <!-- Canales de comunicación -->
                <div class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.canales_comunicacion') }}</span>
                        <select class="form-control" v-model="data.existen_canales_comunicacion" :disabled="readonly">
                        <option :value="true">{{ $t('backend.yes') }}</option>
                        <option :value="false">{{ $t('backend.no') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Tipo de comunicación -->
                <div v-show="data.existen_canales_comunicacion" class="col-md-12">
                    <div class="form-group">
                        <span>{{ $t('comunidad_ficha_inicial.tipo_comunicacion') }}</span>
                        <select class="form-control" v-model="data.tipo_comunicacion" :disabled="readonly">
                        <option disabled value="">Seleccione</option>
                        <option v-for="opcion in opcionesTipoComunicacion" :key="opcion.text" :value="opcion.text">
                                {{ $t('comunidad_ficha_inicial.'+opcion.text) }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import VueTagsInput from '@johmun/vue-tags-input';

export default {
    name: "ficha-comunidad-form",
    components: { VueTagsInput },
    props: ['ficha', 'edicion'],
    data() {
        return {
            readonly: !this.edicion,

            data: {
                cantidad_familias: null,
                cantidad_viviendas: null,
                fecha_formacion: '',
                forma_constitucion: '',
                georeferencia: '',
                anio_inicio_techo: '',
                propietario_actual: '',
                estado_legalizacion: '',
                riesgo_eventos: '',
                riesgo_desalojo: '',
                riesgos_naturales: [],
                factores_antropicos: [],
                material_calles: '',
                acceso_electricidad: '',
                acceso_agua: '',
                manejo_aguas_residuales: '',
                manejo_aguas_pluviales: '',
                material_pisos: '',
                material_paredes: '',
                material_techos: '',
                alumbrado_publico: '',
                equipamientos_presentes: [],
                tiene_organizacion: null,
                liderazgos_democraticos: null,
                anio_eleccion_organizacion: '',
                frecuencia_reunion_organizacion: '',
                actividades_organizacion: '',
                otro_grupo_comunitario: null,
                tipo_otro_grupo: '',
                existen_canales_comunicacion: null,
                tipo_canales_comunicacion: [],
                tipo_comunicacion: null
            },

            tagRiesgoNatural: '',
            autocompleteRiesgosNaturales: [
                { code: 'derrumbes', text: this.$t('comunidad_ficha_inicial.riesgos_naturales_opciones.derrumbes') },
                { code: 'deslizamientos_deslaves', text: this.$t('comunidad_ficha_inicial.riesgos_naturales_opciones.deslizamientos_deslaves') },
                { code: 'ciclon_huracan', text: this.$t('comunidad_ficha_inicial.riesgos_naturales_opciones.ciclon_huracan') },
                { code: 'incendios', text: this.$t('comunidad_ficha_inicial.riesgos_naturales_opciones.incendios') },
                { code: 'inundaciones', text: this.$t('comunidad_ficha_inicial.riesgos_naturales_opciones.inundaciones') },
                { code: 'desbordes_rio', text: this.$t('comunidad_ficha_inicial.riesgos_naturales_opciones.desbordes_rio') },
                { code: 'movimientos_teluricos', text: this.$t('comunidad_ficha_inicial.riesgos_naturales_opciones.movimientos_teluricos') },
                { code: 'ninguno', text: this.$t('comunidad_ficha_inicial.ninguno') },
            ],

                opcionesPropietario: [
                { text: 'estatal_municipal' },
                { text: 'privado' },
                { text: 'colectivo' },
                { text: 'de_cada_familia' }
                ],

                opcionesLegalizacion: [
                { text: 'legal' },
                { text: 'en_proceso_legalizacion' },
                { text: 'no_legal' }
                ],

                opcionesRiesgo: [
                { text: 'alto' },
                { text: 'medio' },
                { text: 'bajo' },
                { text: 'nulo' }
                ],

                opcionesFormaConstitucion: [
                { text: 'toma_espontanea' },
                { text: 'toma_organizada' },
                { text: 'planificada_estado' }
                ],


                autocompleteFactoresAntropicos: [
                    { code: 'relleno_sanitario', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.relleno_sanitario') },
                    { code: 'torres_alta_tension', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.torres_alta_tension') },
                    { code: 'vias_tren', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.vias_tren') },
                    { code: 'vias_vehiculares', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.vias_vehiculares') },
                    { code: 'desechos_industriales', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.desechos_industriales') },
                    { code: 'industria_alto_impacto', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.industria_alto_impacto') },
                    { code: 'actividades_extractivas', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.actividades_extractivas') },
                    { code: 'incendios_intencionados', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.incendios_intencionados') },
                    { code: 'especies_exoticas', text: this.$t('comunidad_ficha_inicial.factores_antropicos_opciones.especies_exoticas') },
                    { code: 'ninguno', text: this.$t('comunidad_ficha_inicial.ninguno') },
                ],

                opcionesMaterialCalles: [
                { text: 'tierra' },
                { text: 'piedra' },
                { text: 'asfalto' },
                { text: 'pavimento' }
                ],

                opcionesElectricidad: [
                { text: 'red_medidor_propio' },
                { text: 'red_medidor_compartido' },
                { text: 'red_sin_medidor' },
                { text: 'anexado_irregular' },
                { text: 'sin_energia' }
                ],

                opcionesAguaPotable: [
                { text: 'red_medidor_propio' },
                { text: 'red_medidor_compartido' },
                { text: 'sistema_mangueras' },
                { text: 'pozo_bomba' },
                { text: 'pozo_sin_bomba' },
                { text: 'carro_repartidor' },
                { text: 'fuente_natural' },
                { text: 'agua_lluvia' },
                { text: 'agua_embotellada' }
                ],

                opcionesAguasResiduos: [
                { text: 'red_cloacal_publica' },
                { text: 'red_cloacal_comunitaria' },
                { text: 'camara_fosa' },
                { text: 'pozo_negro' },
                { text: 'ciego_hoyo' },
                { text: 'ninguno' }
                ],

                opcionesAguasPluviales: [
                { text: 'alcantarillado_publico' },
                { text: 'alcantarillado_comunitario' },
                { text: 'ninguno' }
                ],

                opcionesPisos: [
                { text: 'tierra' },
                { text: 'materiales_desecho_lona' },
                { text: 'carton' },
                { text: 'sacos_plastico' },
                { text: 'madera' },
                { text: 'concreto' },
                { text: 'piso_ceramico' }
                ],

                opcionesParedes: [
                { text: 'materiales_desecho_carton' },
                { text: 'paja' },
                { text: 'lona' },
                { text: 'madera' },
                { text: 'adobe' },
                { text: 'zinc' },
                { text: 'ladrillo_bloque' },
                { text: 'concreto' }
                ],

                opcionesTechos: [
                { text: 'materiales_desecho_lona' },
                { text: 'carton' },
                { text: 'sacos_plastico' },
                { text: 'madera' },
                { text: 'zinc' },
                { text: 'teja_ceramica' },
                { text: 'tejas_plasticas' },
                { text: 'concreto' }
                ],

                opcionesAlumbrado: [
                { text: 'alumbrado_municipal' },
                { text: 'alumbrado_comunidad' },
                { text: 'sin_alumbrado' }
                ],

                opcionesFrecuenciaReunion: [
                { text: 'semanal' },
                { text: 'quincenal' },
                { text: 'mensual' },
                { text: 'bimestral' },
                { text: 'trimestral' },
                { text: 'semestral' }
                ],

                opcionesTipoGrupo: [
                { text: 'politico' },
                { text: 'deportivo' },
                { text: 'religioso' },
                { text: 'cultural' },
                { text: 'educativos' },
                { text: 'jovenes' },
                { text: 'mujeres' }
                ],

                autocompleteEquipamientos: [
                    { code: 'instituciones_educativas', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.instituciones_educativas') },
                    { code: 'cuidado_infancia', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.cuidado_infancia') },
                    { code: 'cuidado_mayores', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.cuidado_mayores') },
                    { code: 'centros_salud', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.centros_salud') },
                    { code: 'espacios_reunion', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.espacios_reunion') },
                    { code: 'espacios_culturales', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.espacios_culturales') },
                    { code: 'espacios_deportivos', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.espacios_deportivos') },
                    { code: 'plazas_parques', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.plazas_parques') },
                    { code: 'senderos_escaleras', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.senderos_escaleras') },
                    { code: 'estaciones_transporte', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.estaciones_transporte') },
                    { code: 'ninguno', text: this.$t('comunidad_ficha_inicial.equipamientos_opciones.ninguno') },
                ],

                autocompleteCanales: [
                { text: 'radio_comunitaria' },
                { text: 'whatsapp' },
                { text: 'carteles' },
                { text: 'voz_a_voz' }
                ],

                opcionesTipoComunicacion: [
                { text: 'whatsapp' },
                { text: 'radio_comunitaria' },
                { text: 'carteles' },
                { text: 'voz_a_voz' }
                ],            
            tagFactorAntropico: '',
          
            tagEquipamiento: '',

            tagCanal: '',
            

        };
    },
    computed: {
        filteredRiesgosNaturales() {
            return this.autocompleteRiesgosNaturales.filter(item =>
                item.text.toLowerCase().includes(this.tagRiesgoNatural.toLowerCase())
            );
        },
        filteredFactoresAntropicos() {
            return this.autocompleteFactoresAntropicos.filter(item =>
                item.text.toLowerCase().includes(this.tagFactorAntropico.toLowerCase())
            );
        },
        filteredEquipamientos() {
            return this.autocompleteEquipamientos.filter(item =>
                item.text.toLowerCase().includes(this.tagEquipamiento.toLowerCase())
            );
        },
        filteredCanales() {
            return this.autocompleteCanales.filter(item =>
                item.text.toLowerCase().includes(this.tagCanal.toLowerCase())
            );
        },


    },
    created() {
        if (this.ficha) {
            this.data = {
                ...this.ficha,
                riesgos_naturales: this.ficha.riesgos_naturales || [],
                materiales_vivienda: this.ficha.materiales_vivienda || [],
                georeferencia: this.ficha.georeferencia || '',
                anio_inicio_techo: this.ficha.anio_inicio_techo || '',
                propietario_actual: this.ficha.propietario_actual || '',
                estado_legalizacion: this.ficha.estado_legalizacion || '',
                riesgo_eventos: this.ficha.riesgo_eventos || '',
                riesgo_desalojo: this.ficha.riesgo_desalojo || '',
                riesgos_naturales: this.ficha.riesgos_naturales || [],
                materiales_vivienda: this.ficha.materiales_vivienda || [],
                georeferencia: this.ficha.georeferencia || '',
                anio_inicio_techo: this.ficha.anio_inicio_techo || '',
                propietario_actual: this.ficha.propietario_actual || '',
                estado_legalizacion: this.ficha.estado_legalizacion || '',
                riesgo_eventos: this.ficha.riesgo_eventos || '',
                riesgo_desalojo: this.ficha.riesgo_desalojo || '',
                alumbrado_publico: this.ficha.alumbrado_publico || '',
                equipamientos_presentes: this.ficha.equipamientos_presentes || [],
                tiene_organizacion: this.ficha.tiene_organizacion ||  null,
                liderazgos_democraticos: this.ficha.liderazgos_democraticos || null,
                anio_eleccion_organizacion: this.ficha.anio_eleccion_organizacion || '',
                frecuencia_reunion_organizacion: this.ficha.frecuencia_reunion_organizacion || '',
                actividades_organizacion: this.ficha.actividades_organizacion || '',
                otro_grupo_comunitario: this.ficha.otro_grupo_comunitario || null,
                tipo_otro_grupo: this.ficha.tipo_otro_grupo || '',
                existen_canales_comunicacion: this.ficha.existen_canales_comunicacion || null,
                tipo_canales_comunicacion: this.ficha.tipo_canales_comunicacion || [],
                tipo_comunicacion: this.ficha.tipo_comunicacion || [],
            };
        }
    }
}
</script>

<style scoped>
.ficha-comunidad-form .form-group {
    margin-bottom: 1.5rem;
}
</style>