<template>
    <div>
        <simplert ref="confirmar"></simplert>
        <div>
            <div class="row">
                <div class="col-md-12">
                    <h2>Bienvenido, {{usernombre}} ({{ user.email }})</h2>
                </div>
            </div>
            <div class="alert alert-success" v-show='guardo'>
                <strong>Los cambios fueron guardados con éxito.</strong>
            </div>
            <div class="row">
                <div class="col-md-12">
                    Aquí podrás realizar cambios en tu pérfil. Modifica tu contraseña, datos personales, etc.
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-5">
                    <h5>Datos personales</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>NOMBRE*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="nombre" id="nombre" v-model="user.nombre">
                            <small class="form-text text-danger">{{validacion.nombre.texto}}&nbsp;<br></small>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.nombre.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                    </div>

                </div>
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>APELLIDO*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="apellido" id="apellido"
                                   v-model="user.apellido">
                            <small class="form-text text-danger">{{validacion.apellido.texto}}&nbsp;<br></small>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.apellido.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>NACIMIENTO*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <datepicker placeholder="Selecciona una fecha" v-model="user.nacimiento" id="nacimiento"
                                        lang="es" format="DD-MM-YYYY"></datepicker>
                            <small class="form-text text-danger">{{validacion.nacimiento.texto}}&nbsp;<br></small>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.nacimiento.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>GENERO*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <b-form-group>
                                <b-form-radio-group id="radios2" v-model="user.sexo">
                                    <b-form-radio value="F">Femenino</b-form-radio>
                                    <b-form-radio value="M">Masculino</b-form-radio>
                                    <b-form-radio value="O">Prefiero no decirlo</b-form-radio>
                                </b-form-radio-group>
                            </b-form-group>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.sexo.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                        <div class="row">
                            <small class="form-text text-danger">{{validacion.sexo.texto}}&nbsp;<br></small>
                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>NRO. DE DNI / PASAPORTE*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="dni" id="dni" v-model="user.dni">
                            <small class="form-text text-danger">{{validacion.dni.texto}}&nbsp;<br></small>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.dni.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>PAIS*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <select id="pais" v-model="user.pais" class="form-control">
                                <option v-for="pais in paises" v-bind:value="pais.id">{{pais.nombre}}</option>
                            </select>
                            <small class="form-text text-danger">{{validacion.pais.texto}}&nbsp;<br></small>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.pais.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>PROVINCIA*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <select id="provincia" v-model="user.provincia" class="form-control">
                                <option v-for="provincia in provincias" v-bind:value="provincia.id">
                                    {{provincia.provincia}}
                                </option>
                            </select>
                            <small class="form-text text-danger">{{validacion.provincia.texto}}&nbsp;<br></small>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.provincia.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>LOCALIDAD*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <select id="localidad" v-model="user.localidad" class="form-control">
                                <option v-for="localidad in localidades" v-bind:value="localidad.id">
                                    {{localidad.localidad}}
                                </option>
                            </select>
                            <small class="form-text text-danger">{{validacion.localidad.texto}}&nbsp;<br></small>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.localidad.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <div class="row">
                        <div class="col-md-12">
                            <label>TELEFONO*</label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-10">
                            <input type="text" class="form-control" name="telefono" id="telefono"
                                   v-model="user.telefono">
                            <small class="form-text text-danger">{{validacion.telefono.texto}}&nbsp;<br></small>
                        </div>
                        <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.telefono.invalido}"><i
                                    class="fas fa-times text-danger"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-5">
                    <h5>Contraseña</h5>
                </div>
            </div>
            <p v-if="loginSocial" class="text-muted">
                Tu cuenta está vinculada a una red social, para cambiar tu contraseña debes
                <a href="#" @click="this.logout">cerrar la sesión</a>
                y hacer click en "<em>Olvidé mi contraseña</em>".
            </p>
            <div class="col-md-5" v-else>
                <div class="row">
                    <div class="col-md-12">
                        <label>CONTRASEÑA ACTUAL*</label>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-10">
                        <input type="password" class="form-control" name="pass_actual" id="pass_actual"
                               v-model="user.pass_actual">
                        <small class="form-text text-danger">{{validacion.pass_actual.texto}}&nbsp;<br></small>
                    </div>
                    <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.pass_actual.valido}"><i
                                    class="fas fa-check text-success"></i></span>
                        <span v-bind:class="{'d-none':!validacion.pass_actual.invalido}"><i
                                class="fas fa-times text-danger"></i></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label>NUEVA CONTRASEÑA*</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10">
                        <input type="password" class="form-control" name="pass" id="pass" v-model="user.pass">
                        <small class="form-text text-danger">{{validacion.pass.texto}}&nbsp;<br></small>
                    </div>
                    <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.pass.valido}"><i
                                    class="fas fa-check text-success"></i></span>
                        <span v-bind:class="{'d-none':!validacion.pass.invalido}"><i
                                class="fas fa-times text-danger"></i></span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label>CONFIRMAR CONTRASEÑA*</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-10">
                        <input type="password" class="form-control" name="pass_confirmacion" id="pass_confirmacion"
                               v-model="user.pass_confirmacion">
                        <small class="form-text text-danger">{{validacion.pass_confirmacion.texto}}&nbsp;<br>
                        </small>
                    </div>
                    <div class="col-md-2">
                            <span v-bind:class="{'d-none':!validacion.pass_confirmacion.valido}"><i
                                    class="fas fa-check text-success"></i></span>
                        <span v-bind:class="{'d-none':!validacion.pass_confirmacion.invalido}"><i
                                class="fas fa-times text-danger"></i></span>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-check">
                        <input v-model="user.recibirMails" class="form-check-input" type="checkbox" id="recibirMails">
                        <label class="form-check-label" for="recibirMails">
                            Recibir notificaciones operativas de la plataforma (necesario para mantenerte informado de las actividades en las que participas)
                        </label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-check">
                        <input v-model="user.acepta_marketing" class="form-check-input" type="checkbox" id="acepta_marketing">
                        <label class="form-check-label" for="acepta_marketing">
                            Acepto que TECHO se contacte conmigo para notificarme de eventos y campañas
                        </label>
                    </div>
                </div>
            </div>
            <br><br>
            <div class="row">
                <div class="col-md-12">
                    <span v-show='volver'>
                        <a href='#' @click="cancelar()" class="btn btn-link">
                            Cancelar
                        </a>
                    </span>
                    <a class="btn btn-primary" href="#" @click="guardar()">Guardar</a> 
                    <a class="btn btn-danger" href="#" @click="eliminar()">Eliminar mi cuenta</a>
                </div>
            </div>
            <hr>
        </div>
    </div>
</template>

<script>
    import _ from 'lodash'

    export default {
        name: 'perfil',
        data: function () {
            var data = {
                guardo: false,
                user: JSON.parse(this.usuario),
                validacion: {},
                paso_actual: 'email',
                volver: true,
                paises: [],
                provincias: [],
                localidades: [],
                message: {
                    danger: false,
                    text: ''
                }
            }
            data.usernombre = data.user.nombre;

            var campos = ['id', 'email', 'nombre', 'apellido', 'nacimiento', 'sexo', 'dni', 'pais', 'provincia', 'localidad', 'telefono', 'facebook_id', 'google_id', 'pass_actual', 'pass', 'pass_confirmacion'];
            for (var i in campos) {
                var campo = campos[i]
                if (!data.user[campo]) data.user[campo] = ''
                data.validacion[campo] = {
                    texto: '',
                    valido: false,
                    invalido: false
                }
            }
            if (data.user.facebook_id || data.user.google_id) {
                data.paso_actual = 'personales'
                data.volver = false
            }
            if (this.linkear) {
                data.paso_actual = 'linkear'
            }
            data.pass = '';
            return data
        },
        props: ['usuario'],
        mounted: function () {
            this.traer_paises()
            this.traer_provincias()
            this.traer_localidades()
        },
        watch: {
            'user.email': function () {
                this.validar_data('email')
            },
            'user.nombre': function () {
                this.validar_data('nombre')
            },
            'user.apellido': function () {
                this.validar_data('apellido')
            },
            'user.nacimiento': function () {
                this.validar_data('nacimiento')
            },
            'user.sexo': function () {
                this.validar_data('sexo')
            },
            'user.dni': function () {
                this.validar_data('dni')
            },
            'user.telefono': function () {
                this.validar_data('telefono')
            },
            'user.pass': function () {
                this.validar_data('pass')
            },
            'user.pass_confirmacion': function () {
                this.validar_data('pass_confirmacion')
            },
            'user.pais': function () {
                this.traer_provincias()
            },
            'user.provincia': function () {
                this.traer_localidades()
            }
        },
        methods: {
            cancelar: function () {
                axios.get('/ajax/usuario/perfil').then(response => {
                    this.user = response.data.data
                    this.limpia_validacion_pass(this.user)
                })
            },
            registro_facebook: function () {
                window.location.href = 'https://actividades.techo.org/auth/facebook';
            },
            registro_google: function () {
                window.location.href = 'https://actividades.techo.org/auth/google';
            },
            guardar: function () {
                this.guardo = false;
                var data = this.user
                this.limpia_validacion_pass(data)
                axios.put('/ajax/usuario', this.user).then(response => {
                    this.guardo = true;
                    this.$parent.$refs.login.user.nombres = this.user.nombre
                }).catch((error) => {
                    this.validar_data()
                });
            },
            confirma_linkear: function () {
                var media = '';
                var id = '';
                if (this.google_id) {
                    media = 'google';
                    id = this.google_id;
                }
                if (this.facebook_id) {
                    media = 'facebook';
                    id = this.facebook_id;
                }
                axios.put('/ajax/usuario/linkear', {
                    media: media,
                    id: id,
                    email: this.email
                }).then(response => {
                    if (response.data.login_callback) window.location.href = response.data.login_callback;
                })
            },
            paso: function (paso) {
                return paso == this.paso_actual
            },
            limpia_validacion_pass: function (data) {
                if (!this.user.pass && !this.user.pass_actual && !this.user.pass_confirmacion) {
                    delete data.pass;
                    delete data.pass_confirmacion;
                    delete data.pass_actual;
                    this.validacion.pass.texto = ''
                    this.validacion.pass.valido = false
                    this.validacion.pass.invalido = false
                    this.validacion.pass_actual.texto = ''
                    this.validacion.pass_actual.valido = false
                    this.validacion.pass_actual.invalido = false
                    this.validacion.pass_confirmacion.texto = ''
                    this.validacion.pass_confirmacion.valido = false
                    this.validacion.pass_confirmacion.invalido = false
                }
            },
            validar_data: _.debounce(function (prop) {
                this.guardo = false;
                var data = {}
                if (prop) {
                    data[prop] = this.user[prop]
                    data.id = this.user.id
                    this.validacion[prop].valido = false
                    this.validacion[prop].invalido = false
                    if (prop == 'pass_confirmacion') {
                        data['pass'] = this.user.pass
                    }
                } else {
                    data = this.user
                    this.limpia_validacion_pass(data)
                }
                axios.get('/ajax/usuario/validar/update', {params: data})
                    .then(response => {
                        var params = response.data.params
                        for (var i in params) {
                            prop = params[i]
                            this.validacion[prop].texto = ''
                            if (this.user[prop]) {
                                this.validacion[prop].valido = true
                                this.validacion[prop].invalido = false
                            }
                        }
                    })
                    .catch(error => {
                        var errors = error.response.data.errors
                        for (var prop in errors) {
                            this.validacion[prop].texto = errors[prop][0]
                            this.validacion[prop].valido = false
                            this.validacion[prop].invalido = true
                        }
                    })
            }, 500),
            traer_paises: function () {
                axios.get('/ajax/paises').then(response => {
                    this.paises = response.data
                })
            },
            traer_provincias: function () {
                if (this.user.pais) {
                    axios.get('/ajax/paises/' + this.user.pais + '/provincias').then(response => {
                        this.provincias = response.data
                    })
                }
            },
            traer_localidades: function () {
                if (this.user.pais && this.user.provincia) {
                    axios.get('/ajax/paises/' + this.user.pais + '/provincias/' + this.user.provincia + '/localidades').then(response => {
                        this.localidades = response.data
                    })
                }
            },
            logout: function (e) {
                e.preventDefault();
                events.$emit('cerrar-sesion');
            },
            eliminar: function () {
                let self = this;
                self.$refs.confirmar.openSimplert({
                    title:'ELIMINAR MI CUENTA',
                    message:"Estás por eliminar tu cuenta de esta plataforma. La acción no podrá deshacerse. ¿Deseas continuar?",
                    useConfirmBtn: true,
                    isShown: true,
                    disableOverlayClick: true,
                    customClass: 'confirmar',
                    customCloseBtnText: 'CANCELAR', //string -- close button text
                    customCloseBtnClass: 'btn btn-secondary', //string -- custom class for close button
                    customConfirmBtnText: 'SI, ELIMINAR', //string -- confirm button text
                    customConfirmBtnClass: 'btn btn-danger', //string -- custom class for confirm button
                    onConfirm: function() {
                        axios.delete('/ajax/usuario').then(response => {
                            window.location.href = '/';
                        })
                    }
                })
            }

        },
        computed: {
            loginSocial: function () {
                return $.inArray(this.user.facebook_id, ['', null])  === -1 ||
                    $.inArray(this.user.google_id, ['', null]) === -1;
            }
        }
    }
</script>

<style scoped>
    .btn-secondary {
        text-transform: uppercase !important;
        font-weight: bold !important;
    }

    .btn-danger {
        text-transform: uppercase !important;
        font-weight: bold !important;
    }

</style>