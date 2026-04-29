<template>
    <div class="actividad-informe-cierre-form">
        <div v-show="guardado" class="callout callout-success">
            <h4>{{ mensajeGuardado }}</h4>
        </div>
        <simplert ref="loading"></simplert>

        <div v-show="tieneErrores" class="callout callout-danger">
            <h4>{{ $t('backend.errors') }}:</h4>
            <ul>
                <li v-for="error in validationErrors">{{ error[0] }}</li>
            </ul>
        </div>

        <div class="box">
            <div class="box-body">
                <!-- Comunidad -->
                <div class="row" v-if="actividad.comunidades && actividad.comunidades.length">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="idComunidad">Comunidad</label>
                            <select id="idComunidad" class="form-control" v-model="data.idComunidad" :disabled="readonly">
                                <option :value="null">-- Sin comunidad --</option>
                                <option v-for="c in actividad.comunidades" :key="c.idComunidad" :value="c.idComunidad">{{ c.nombre }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Número de participantes -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="numero_participantes">{{ $t('backend.numero_participantes') }}</label>
                            <input type="number" id="numero_participantes" class="form-control"
                                   v-model="data.numero_participantes" :disabled="readonly">
                        </div>
                    </div>
                    <!-- Número de beneficiados -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="numero_beneficiados">{{ $t('backend.numero_beneficiados') }}</label>
                            <input type="number" id="numero_beneficiados" class="form-control"
                                   v-model="data.numero_beneficiados" :disabled="readonly">
                        </div>
                    </div>

                    <!-- Programa -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="programa">{{ $t('backend.programa') }}</label>
                            <select id="programa" class="form-control" v-model="data.programa" :disabled="readonly">
                                <option v-for="(label, key) in opcionesPrograma" :value="key">{{ label }}</option>
                            </select>
                        </div>
                    </div>

                   
                </div>
                <div class="row">
                     <!-- Soluciones entregadas -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="soluciones_entregadas">{{ $t('backend.solucion_entregada') }}</label>
                            <select id="soluciones_entregadas" class="form-control" v-model="data.soluciones_entregadas" :disabled="readonly">
                                <option v-for="(label, key) in opcionesSoluciones" :value="key">{{ label }}</option>
                            </select>
                        </div>
                    </div> 
                </div> 
                <label class="font-weight-bold mt-3">
                                
                                {{ $t('backend.soluciones_entregadas_por') }}
                            </label>
                <div class="row">
                        <!-- Cantidad de soluciones entregadas por grupo -->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cant_soluciones_voluntariado">{{ $t('backend.tipo_voluntariado_options.voluntariado') }}</label>
                             <input type="number" id="cant_soluciones_voluntariado" class="form-control"
                                   v-model="data.cant_soluciones_voluntariado" :disabled="readonly">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cant_soluciones_corporativos">{{ $t('backend.tipo_voluntariado_options.corporativos') }}</label>
                             <input type="number" id="cant_soluciones_corporativos" class="form-control"
                                   v-model="data.cant_soluciones_corporativos" :disabled="readonly">
                        </div>
                    </div>


                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cant_soluciones_secundarios">{{ $t('backend.tipo_voluntariado_options.secundarios') }}</label>
                             <input type="number" id="cant_soluciones_secundarios" class="form-control"
                                   v-model="data.cant_soluciones_secundarios" :disabled="readonly">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cant_soluciones_universitarios">{{ $t('backend.tipo_voluntariado_options.universitarios') }}</label>
                             <input type="number" id="cant_soluciones_universitarios" class="form-control"
                                   v-model="data.cant_soluciones_universitarios" :disabled="readonly">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cant_soluciones_familias">{{ $t('backend.tipo_voluntariado_options.familias_amigos') }}</label>
                             <input type="number" id="cant_soluciones_familias" class="form-control"
                                   v-model="data.cant_soluciones_familias" :disabled="readonly">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cant_soluciones_familias">TOTAL</label>
                            <div>
                                <span class="badge bg-success" style="font-size: 18px; padding: 8px 16px;">
                                    {{ totalSoluciones }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

               <div class="row"> 
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="quienes_financiaron">{{ $t('backend.quienes_financiaron') }}</label>
                            <input type="text" id="quienes_financiaron" class="form-control"
                                   v-model="data.quienes_financiaron" :disabled="readonly">
                        </div>
                    </div>
                </div>
                   

                <!-- Comentarios adicionales -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="comentarios_adicionales">{{ $t('backend.comentarios_adicionales') }}</label>
                            <textarea id="comentarios_adicionales" class="form-control" rows="3"
                                      v-model="data.comentarios_adicionales" :disabled="readonly"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Archivos adicionales -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="link_adicional">{{ $t('backend.link_adicional') }}</label>
                            <input type="text" id="link_adicional" class="form-control"
                                   v-model="data.link_adicional" :disabled="readonly">
                        </div>
                    </div>
                </div>

                <div class="alligned-buttons pull-right">
                    <button class="btn btn-primary" @click.prevent="guardar" :disabled="readonly">{{ $t('backend.save') }}</button>                   
                    <button ref="eliminar" v-show="!readonly" class="btn btn-danger" @click.prevent="eliminar" >{{ $t('backend.eliminate') }}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "ActividadInformeCierreForm",
    props: ['informe', 'actividad', 'edicion'],
    data() {
        return {
            data: {
                idActividad: this.actividad.idActividad,
                idComunidad: null,
                numero_participantes: null,
                programa: null,
                soluciones_entregadas: null,
                numero_beneficiados: null,
                quienes_financiaron: '',
                link_adicional: '',
                comentarios_adicionales: '',
                cant_soluciones_voluntariado: null,
                cant_soluciones_secundarios: null,
                cant_soluciones_corporativos: null,
                cant_soluciones_universitarios: null,
                cant_soluciones_familias: null,
            },
            readonly: !this.edicion,
            guardado: false,
            mensajeGuardado: '',
            validationErrors: {},

            opcionesPrograma: this.$t('backend.programa_options'),
            opcionesSoluciones: this.$t('backend.soluciones_entregadas_options'),
        }
    },
    mounted() {
        this.data = this.informe;
    },
    computed: {
        tieneErrores() {
            return Object.keys(this.validationErrors).length > 0;
        },
        totalSoluciones() {
            return (
                (parseInt(this.data.cant_soluciones_voluntariado) || 0) +
                (parseInt(this.data.cant_soluciones_corporativos) || 0) +
                (parseInt(this.data.cant_soluciones_secundarios) || 0) +
                (parseInt(this.data.cant_soluciones_universitarios) || 0) +
                (parseInt(this.data.cant_soluciones_familias) || 0)
            );
        }
    },
    methods: {
        guardar() {
            let url;
            this.validationErrors = [];
            url = `/admin/ajax/actividades/`+this.actividad.idActividad+`/informe_cierre`;
            axios.post(url, this.data)
                .then((respuesta) => {
                    this.validationErrors = [];
                    this.$refs.loading.justCloseSimplert();
                    this.$emit('saved', respuesta.data);
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.validationErrors = Object.values(error.response.data.errors);
                            Event.$emit('error');

                        }
                    }
                });
        },
        eliminar() {
            let url;
            this.validationErrors = [];
            url = `/admin/ajax/actividades/`+this.actividad.idActividad+`/informe_cierre/`+this.data.idActividadInformeCierre;
            axios.delete(url)
                .then((respuesta) => {
                    this.validationErrors = [];
                    this.$refs.loading.justCloseSimplert();
                    this.$emit('saved', respuesta.data);
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.status === 422) {
                            this.validationErrors = Object.values(error.response.data.errors);
                            Event.$emit('error');

                        }
                    }
                });
        }
    }
}
</script>
