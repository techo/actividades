<template>
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title">Información General</h3>
        </div>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="categoria">Categoría</label>
                        <select id="categoria" name="categoria"
                               class="form-control"
                               v-bind:disabled="readonly"
                               v-model="categoriaSeleccionada"
                        >
                            <option v-for="cat in categorias" v-bind:value="cat.id">{{ cat.nombre }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tipo">Tipo de Actividad</label>
                        <input id="tipo" name="tipo"
                               type="text"
                               class="form-control"
                               value="algo saraza"
                               v-bind:disabled="readonly"
                        >
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombreActividad">Nombre de Actividad</label>
                        <input id="nombreActividad"
                               type="text"
                               class="form-control"
                               value="algo saraza"
                               v-bind:disabled="readonly"
                        >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="pais">País</label>
                        <select id="pais" name="pais"
                               type="text"
                               class="form-control"
                               value="algo saraza"
                               v-bind:disabled="readonly"
                        >
                            <option v-for="pais in paises" v-bind:value="pais.id">{{ pais.nombre }}</option>
                        </select>
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="provincia">Provincia</label>
                        <input id="provincia"
                               type="text"
                               class="form-control"
                               value="algo saraza"
                               v-bind:disabled="readonly"
                        >
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="localidad">Localidad</label>
                        <input id="localidad"
                               type="text"
                               class="form-control"
                               value="algo saraza"
                               v-bind:disabled="readonly"
                        >
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tipoActividad">Fecha / Duración</label>
                        <p>15/01/2018 10:00AM - 17/01/2018 05:00PM</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cupo">Cupo</label>
                        <input id="cupo"
                               type="number"
                               class="form-control"
                               value="algo saraza"
                               v-bind:disabled="readonly"
                        >
                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="tipoActividad">Descripción</label>
                        <p>I'm here to ensure the League of Shadow fulfills its duty to restore balance to civilization. You yourself fought the decadence of Gotham for years with all your strength, all your resources, all your moral authority. And the only victory you achieved was a lie. Now, you understand? Gotham is beyond saving and must be allowed to die.</p>
                    </div>
                </div>

            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tipoActividad">Punto de Encuentro</label>
                        <p>Constitución, frente a la boletería</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tipoActividad">Horario</label>
                        <p>07:00AM</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tipoActividad">Coordinador</label>
                        <p>Alejandro Rao</p>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group"><br>
                        <p style="color: red">Borrar</p>
                    </div>
                </div>
            </div>

        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            <button type="button" class="btn">Volver al listado</button>
            <button type="button" class="btn btn-danger pull-right">Cancelar</button>

            <button type="button" class="btn btn-primary pull-right">Editar</button>
        </div>
        <!-- box-footer -->
    </div>
    <!-- /.box -->

</template>

<script>
    import axios from 'axios';
    export default {
        name: "actividades-show",
        props: ['actividad'],
        data() {
            return {
                dataActividad: {},
                readonly: true,
                paises: [],
                paisSeleccionado: '',
                categorias: [],
                categoriaSeleccionada: ''
            }
        },
        created() {
            this.dataActividad = JSON.parse(this.actividad);
            this.getPaises();
        },
        methods: {
            getPaises() {
                this.axiosGet('/ajax/paises', function (data, self) {
                    self.paises = data;
                    self.paisSeleccionado = self.dataActividad.idPais
                })
            },

            axiosGet(url, fCallback, params = []) {
                axios.get(url, params)
                    .then(response => {
                        fCallback(response.data, this)
                    })
                    .catch((error) => {
                        // Error
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.log(error.response.data);
                            console.log(error.response.status);
                            console.log(error.response.headers);
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.log(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.log('Error', error.message);
                        }
                        console.log(error.config);
                    });

            }
        }
    }
</script>

<style scoped>

</style>