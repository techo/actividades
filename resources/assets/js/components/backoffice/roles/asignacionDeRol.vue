<template>
    <span>
        <div v-show="actualizado" class="callout callout-success">
            <h4>{{ mensaje }}</h4>
        </div>
        <div class="box">
            <div class="box-body  with-border">
                <!--<form :action="urlUpdate" method="post">-->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="usuario">Usuario</label>
                                <v-select
                                        :options="dataUsuarios"
                                        label="nombre"
                                        placeholder="Seleccione"
                                        name="usuario"
                                        id="usuario"
                                        v-model="usuarioSeleccionado"
                                        :filterable=false
                                        @search="onSearchUsuario"
                                >
                                </v-select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="rol">Rol</label>
                                <v-select
                                        :options="dataRoles"
                                        label="rol"
                                        placeholder="Seleccione"
                                        name="rol"
                                        id="rol"
                                        v-model="rolSeleccionado"
                                >
                                </v-select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <br>
                            <button class="btn btn-primary btn-lg" @click="this.actualizar">Actualizar</button>
                        </div>
                    </div>
                <!--</form>-->
            </div>
            <!-- /.box-body -->
        </div>
    </span>
</template>

<script>
    import axios from 'axios';
    import _ from 'lodash';

    export default {
        name: "asignacion-de-rol",
        props: ['roles'],
        data(){
            return {
                usuarioSeleccionado: undefined,
                rolSeleccionado: undefined,
                dataUsuarios: [],
                dataRoles: [],
                urlUpdate: "",
                token: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                actualizado: false,
                mensaje: ""
            }
        },
        created(){
            this.dataRoles = JSON.parse(this.roles);
        },
        watch: {
            usuarioSeleccionado() {
                this.getRolActual();
            }
        },
        methods: {
            onSearchUsuario: function (text, loading) {
                loading(true);
                this.searchUsuario(loading, text, this);
            },
            searchUsuario: _.debounce((loading, search, vm) => {
                let fetchData = {
                    headers: new Headers()
                };
                fetchData.headers.set('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                fetch(
                    `/admin/ajax/search/usuarios?usuario=${escape(search)}`
                , fetchData).then(res => {
                    res.json().then(json => (vm.dataUsuarios = json));
                    loading(false);
                });
            }, 1000),
            getRolActual: function () {
                if (this.usuarioSeleccionado !== undefined) {
                    this.urlUpdate = "/admin/roles/usuario/" + this.usuarioSeleccionado.idPersona;
                    this.axiosGet('/admin/ajax/usuarios/' + this.usuarioSeleccionado.idPersona + '/rol',
                        function (data, self) {
                            self.rolSeleccionado = data.data;
                        });
                }
            },
            actualizar: function () {
                if(this.rolSeleccionado !== undefined) {
                    let dataForm = {
                        rolID: this.rolSeleccionado.id
                    };
                    this.axiosPost(this.urlUpdate, function (data, self) {
                            self.actualizado = true;
                            self.mensaje = data;
                    }, dataForm);
                }
            },
            axiosPost(url, fCallback, params = []) {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                axios.post(url, params)
                    .then(response => {
                        fCallback(response.data, this);
                        Event.$emit('success');
                        this.readonly = true;
                    })
                    .catch((error) => {
                        Event.$emit('error');
                        // Error
                        console.info('Error en: ' + url);
                        console.error(error.response.status);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            // console.error(error.response.data);
                            // console.error(error.response.status);
                            // console.error(error.response.headers);
                            if (error.response.status === 422) {
                                // debugger;
                                this.validationErrors = Object.values(error.response.data);
                            }
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.error(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });

            },
            axiosGet(url, fCallback, params = []) {
                axios.get(url, params)
                    .then(response => {
                        fCallback(response.data, this)
                    })
                    .catch((error) => {
                        // Error
                        console.error('Error en: ' + url);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.error(error.response.data);
                            console.error(error.response.status);
                            console.error(error.response.headers);
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.error(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });

            },
        },
    }
</script>

<style scoped>

</style>