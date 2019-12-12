<template>
    <div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Básica</h3>
            </div>
            <div class="box-body">
                
                <div class="row">

                    <div class="col-md-4">
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

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <input id="estado" name="estado"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.estadoConstruccion"
                                   required
                            >
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

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="oficina">Oficina</label>
                            <input id="oficina" name="idOficina"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.idOficina"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <input id="tipo" name="idTipo"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.idTipo"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="coordinador">Coordinador</label>
                            <input id="coordinador" name="idMunicipalidad"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.idCoordinador"
                                   required
                            >
                        </div>
                    </div>

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
                            <input id="pais" name="idPais"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.idPais"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="provincia">Provincia</label>
                            <input id="provincia" name="idProvincia"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.idProvincia"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="localidad">Localidad</label>
                            <input id="localidad" name="idLocalidad"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.idLocalidad"
                                   required
                            >
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Inscripciones</h3>
            </div>
            <div class="box-body">

                <div class="row">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="limiteInscripciones">Límite inscripciones</label>
                            <input id="limiteInscripciones" name="limiteInscripciones"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.limiteInscripciones"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="pago">Confirmación por pago</label>
                            <input id="pago" name="pago"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.pago"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="confirmacion">Confirmación manual</label>
                            <input id="confirmacion" name="confirmacion"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.confirmacion"
                                   required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="inscripcionInterna">Pública</label>
                            <input id="inscripcionInterna" name="inscripcionInterna"
                                   type="text"
                                   class="form-control"
                                   v-model="actividad.inscripcionInterna"
                                   required
                            >
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="mensajeInscripcion">Mensaje de confirmación</label>
                            <textarea name="descripcion" v-model="actividad.mensajeInscripcion" class="form-control" required ></textarea>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "actividad",
        props: ['id'],
        components: {},
        data() {
            return {
                actividad: {},
            }
        },
        mounted() {
            Event.$on('guardar', this.guardar);

            axios.get('/admin/ajax/actividades/' + this.id)
                .then((datos) => { this.actividad = datos.data; })
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
        }
    }
</script>

<style scoped>

</style>