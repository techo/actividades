<template>
    <div class="card opacidad-90" >
        <simplert ref="confirmar"></simplert>
        <div class="card-body">
            <div class="row justify-content-center m-2  text-center">
                   
            </div>
            <div  class="row align-items-center m-2  text-center">
                <div class="col-md-1 ">
                </div>

                <div class="col-md-5 ">
                    <div v-show="!openPhotoEdit">
                        <img v-if="user.photo != null" class="imagen-perfil-redonda" :src="'/' + user.photo" alt="Foto">
                        <img v-else src="/bower_components/admin-lte/dist/img/user_avatar.png" class="imagen-perfil-redonda" alt="User Image">
                    
                        <button class="btn btn-light btn-circle edit-button mt-3 position-absolute top-50 start-50 translate-middle" @click="selectPhoto">
                            <i class="fa fa-edit"></i>
                        </button>
                    </div>
                    <photoEdit :openPhotoEdit="openPhotoEdit" :photoPerfil="user.photo" @updatePhoto="updatePhoto">
                    </photoEdit >
                </div>
                <div class="col-md-5">
                    <h3>{{ $t('frontend.welcome_'+user.genero ) }}, {{ usernombre }}</h3>
                    <h4>({{ user.email }})</h4>
                </div>
                <div class="col-md-1 ">
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <p class="text-center m-2">{{ $t('frontend.profile_text_1') }}</p>
                    <p class="text-center m-2">{{ $t('frontend.profile_text_2') }}
                        <a href="/perfil/cambiar_email">
                            {{ $t('frontend.profile_text_3') }}</a>.
                    </p>
                </div>
            </div>

            <div>
                <b-tabs content-class="mt-3" fill v-model="paso_actual">
                    <b-tab class="m-3" :title="$t('frontend.personal_data')" href="#personales">
                        <div class="row mx-2">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.name') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="nombre" id="nombre"
                                            v-model="user.nombre">
                                        <small class="form-text text-danger">{{ validacion.nombre.texto
                                        }}&nbsp;<br></small>
                                    </div>
                                    <div class="col-md-2">
                                        <span v-bind:class="{ 'd-none': !validacion.nombre.invalido }"><i
                                                class="fas fa-times text-danger"></i></span>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.surname') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="apellido" id="apellido"
                                            v-model="user.apellido">
                                        <small class="form-text text-danger">{{ validacion.apellido.texto
                                        }}&nbsp;<br></small>
                                    </div>
                                    <div class="col-md-2">
                                        <span v-bind:class="{ 'd-none': !validacion.apellido.invalido }"><i
                                                class="fas fa-times text-danger"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mx-2">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.birth_date') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <datepicker class="w-100" placeholder="Selecciona una fecha" v-model="user.nacimiento"
                                            id="nacimiento" lang="es" format="DD-MM-YYYY"></datepicker>
                                        <small class="form-text text-danger">{{ validacion.nacimiento.texto
                                        }}&nbsp;<br></small>
                                    </div>
                                    <div class="col-md-2">
                                        <span v-bind:class="{ 'd-none': !validacion.nacimiento.invalido }"><i
                                                class="fas fa-times text-danger"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.gender') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <b-form-group>
                                            <b-form-radio-group id="radios2" v-model="user.genero">
                                                <b-form-radio value="F">{{ $t('frontend.gender_f') }}</b-form-radio>
                                                <b-form-radio value="M">{{ $t('frontend.gender_m') }}</b-form-radio>
                                                <b-form-radio value="X">{{ $t('frontend.gender_x') }}</b-form-radio>
                                                <!-- <b-form-radio value="O">{{ $t('frontend.gender_o') }}</b-form-radio> -->
                                            </b-form-radio-group>
                                        </b-form-group>
                                    </div>
                                    <div class="col-md-2">
                                        <span v-bind:class="{ 'd-none': !validacion.genero.invalido }"><i
                                                class="fas fa-times text-danger"></i></span>
                                    </div>
                                    <div class="row">
                                        <small class="form-text text-danger">{{ validacion.genero.texto
                                        }}&nbsp;<br></small>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="row mx-2">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.passport') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <input type="text" class="form-control" name="dni" id="dni" v-model="user.dni">
                                        <small class="form-text text-danger">{{ validacion.dni.texto
                                        }}&nbsp;<br></small>
                                    </div>
                                    <div class="col-md-2">
                                        <span v-bind:class="{ 'd-none': !validacion.dni.invalido }"><i
                                                class="fas fa-times text-danger"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.country') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <select id="pais" v-model="user.pais" class="form-control">
                                            <option v-for="pais in paises" v-bind:value="pais.id">{{ pais.nombre }}
                                            </option>
                                        </select>
                                        <small class="form-text text-danger">{{ validacion.pais.texto
                                        }}&nbsp;<br></small>
                                    </div>
                                    <div class="col-md-2">
                                        <span v-bind:class="{ 'd-none': !validacion.pais.invalido }"><i
                                                class="fas fa-times text-danger"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mx-2">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.state') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <select id="provincia" v-model="user.provincia" class="form-control">
                                            <option v-for="provincia in provincias" v-bind:value="provincia.id">
                                                {{ provincia.provincia }}
                                            </option>
                                        </select>
                                        <small class="form-text text-danger">{{ validacion.provincia.texto
                                        }}&nbsp;<br></small>
                                    </div>
                                    <div class="col-md-2">
                                        <span v-bind:class="{ 'd-none': !validacion.provincia.invalido }"><i
                                                class="fas fa-times text-danger"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.municipality') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                        <select id="localidad" v-model="user.localidad" class="form-control">
                                            <option v-for="localidad in localidades" v-bind:value="localidad.id">
                                                {{ localidad.localidad }}
                                            </option>
                                        </select>
                                        <small class="form-text text-danger">{{ validacion.localidad.texto
                                        }}&nbsp;<br></small>
                                    </div>
                                    <div class="col-md-2">
                                        <span v-bind:class="{ 'd-none': !validacion.localidad.invalido }"><i
                                                class="fas fa-times text-danger"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mx-2">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label>{{ $t('frontend.telephone') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-10">
                                    <!-- <vue-tel-input
                                        v-model="user.telefono"
                                        @input="validatePhoneNumber"
                                        :preferred-countries="['us', 'ca', 'mx', 'ar', 'es', 'fr', 'uk', 'in']"
                                        placeholder="Enter phone number"
                                    /> -->
                                    <VueTelInput v-model="phoneNumber" @input="validatePhoneNumber"
                                        @country-changed="handleCountryChange"
                                        :preferredCountries="['ar', 'co', 'mx', 'pe', 'py', 'ur', 'br', 'cl']"
                                        placeholder="Enter phone number"
                                        :disabledFetchingCountry="true"
                                        ref="telInput">
                                    </VueTelInput>
                                    <small class="form-text text-danger">{{ validacion.telefono.texto }}&nbsp;<br></small>
                                    </div>
                                    <div class="col-md-2">
                                    <span v-bind:class="{ 'd-none': !validacion.telefono.invalido }">
                                        <i class="fas fa-times text-danger"></i>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mx-2">
                            <div class="col-md-6">
                                <h5>{{ $t('frontend.password') }}</h5>
                            </div>
                        </div>
                        <p v-if="loginSocial" class="text-muted mx-2 px-2">
                            {{ $t('frontend.account_rrss_text_1') }}
                            <a href="#" @click="this.logout">{{ $t('frontend.logout') }}</a>
                            {{ $t('frontend.account_rrss_text_2') }}
                            <em>{{ $t('frontend.forget_password') }}</em>
                        </p>
                        <div class="col-md-5 mx-2" v-else>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>{{ $t('frontend.actual_password') }}</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-10">
                                    <input type="password" class="form-control" name="pass_actual" id="pass_actual"
                                        v-model="user.pass_actual">
                                    <small class="form-text text-danger">{{ validacion.pass_actual.texto
                                    }}&nbsp;<br></small>
                                </div>
                                <div class="col-md-2">
                                    <span v-bind:class="{ 'd-none': !validacion.pass_actual.valido }"><i
                                            class="fas fa-check text-success"></i></span>
                                    <span v-bind:class="{ 'd-none': !validacion.pass_actual.invalido }"><i
                                            class="fas fa-times text-danger"></i></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>{{ $t('frontend.new_password') }}</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10">
                                    <input type="password" class="form-control" name="pass" id="pass"
                                        v-model="user.pass">
                                    <small class="form-text text-danger">{{ validacion.pass.texto }}&nbsp;<br></small>
                                </div>
                                <div class="col-md-2">
                                    <span v-bind:class="{ 'd-none': !validacion.pass.valido }"><i
                                            class="fas fa-check text-success"></i></span>
                                    <span v-bind:class="{ 'd-none': !validacion.pass.invalido }"><i
                                            class="fas fa-times text-danger"></i></span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label>{{ $t('frontend.confirm_new_password') }}</label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-10">
                                    <input type="password" class="form-control" name="pass_confirmacion"
                                        id="pass_confirmacion" v-model="user.pass_confirmacion">
                                    <small class="form-text text-danger">{{ validacion.pass_confirmacion.texto
                                    }}&nbsp;<br>
                                    </small>
                                </div>
                                <div class="col-md-2">
                                    <span v-bind:class="{ 'd-none': !validacion.pass_confirmacion.valido }"><i
                                            class="fas fa-check text-success"></i></span>
                                    <span v-bind:class="{ 'd-none': !validacion.pass_confirmacion.invalido }"><i
                                            class="fas fa-times text-danger"></i></span>
                                </div>
                            </div>

                        </div>

                        <div class="row mx-2">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input v-model="user.recibirMails" class="form-check-input" type="checkbox"
                                        id="recibirMails">
                                    <label class="form-check-label" for="recibirMails">
                                        {{ $t('frontend.platform_notifications_agreement') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mx-2">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input v-model="user.acepta_marketing" class="form-check-input" type="checkbox"
                                        id="acepta_marketing">
                                    <label class="form-check-label" for="acepta_marketing">
                                        {{ $t('frontend.techo_notifications_agreement') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row alert alert-success m-2" v-show='guardo'>
                            <strong>{{ $t('frontend.changes_success') }}</strong>
                        </div>
                        <div class="row m-2">
                            <div class="col-md-12">
                                <span v-show='volver'>
                                    <button @click="cancelar()" class="btn btn-link m-2" :disabled="!formDirty">
                                        {{ $t('frontend.cancel') }}
                                    </button>
                                </span>
                                <button class="btn btn-primary m-2" href="#" @click="guardar()" :disabled="!formDirty">
                                    {{
                                        $t('frontend.save')
                                }}</button>
                                <button class="btn btn-danger m-2" href="#" @click="eliminar()">{{
                                        $t('frontend.delete_account')
                                }}</button>
                            </div>
                        </div>
                        <hr>

                    </b-tab>
                    <b-tab href="#ficha" class="w-100 text-uppercase" :title="$t('frontend.ficha_medica')">
                        <ficha-medica ref="fichaMedica" :fichaMedica="user.fichaMedica" />
                    </b-tab>
                    <b-tab href="#estudios" :title="$t('frontend.estudios')">
                        <estudios ref="estudios" :estudios="user.estudios" :idPersona="user.id"/>
                    </b-tab>

                    <b-tab href="#equipos" :title="$t('frontend.equipos')">
                        <equipos ref="equipos" :equipos="user.integrantes" :idPersona="user.id"/>
                    </b-tab>

                    <!-- <b-tab href="#otros" :title="$t('frontend.otros_datos')">
                        <ficha-medica ref="fichaMedica" :fichaMedica="user.fichaMedica" />
                    </b-tab> -->
                </b-tabs>
            </div>
        </div>
    </div>
</template>

<script>
import _ from 'lodash'
import photoEdit from './photoEdit';
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/dist/vue-tel-input.css';
import { parsePhoneNumberFromString } from 'libphonenumber-js';

export default {
    components: {photoEdit, VueTelInput},
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
            formDirty: false,
            photo: null,
            nombre_photo: '',
            message: {
                danger: false,
                text: ''
            },
            openPhotoEdit: false,
            phoneNumber: '',
            previousCountry: '',
        }
        data.tabIndex = 0, 
        data.tabs = ['#datos', '#ficha', '#estudios'],
        data.usernombre = data.user.nombre;

        var campos = ['id', 'email', 'nombre', 'apellido', 'nacimiento', 'genero', 'dni', 'pais', 'provincia', 'localidad', 'telefono', 'facebook_id', 'google_id', 'pass_actual', 'pass', 'pass_confirmacion'];
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
        this.formDirty = false
        this.phoneNumber = this.user.telefono;
        this.paso_actual = (window.location.href.split('#')[1]);
        this.removeUndefinedText();
    },
    updated() {
        this.removeUndefinedText();
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
        'user.genero': function () {
            this.validar_data('genero')
        },
        'user.dni': function () {
            this.validar_data('dni')
        },
        'phoneNumber': function () {
            this.user.telefono = this.phoneNumber.replace(/[\s-]+/g, '');
            this.validar_data('telefono')
        },
        'user.pass': function () {
            this.validar_data('pass')
        },
        'user.pass_confirmacion': function () {
            this.validar_data('pass_confirmacion')
        },
        'user.pais': function () {
            this.traer_provincias();
            this.user.provincia = null;
            this.validar_data('provincia')
            this.formDirty = true;
        },
        'user.provincia': function () {
            this.traer_localidades();
            this.formDirty = true;
        },
        'user.localidad': function () {
            this.formDirty = true;
        },
        'user.recibirMails': function () {
            this.formDirty = true;
        },
        'user.acepta_marketing': function () {
            this.formDirty = true;
        }

    },
    methods: {
        validatePhoneNumber() {
            // try {
            //     const phoneNumber = parsePhoneNumberFromString(this.user.telefono);
            //     if (phoneNumber && phoneNumber.isValid()) {
            //     this.validacion.telefono.invalido = false;
            //     this.validacion.telefono.texto = '';
            //     } else {
            //    // this.validacion.telefono.invalido = true;
            //     this.validacion.telefono.texto = 'Invalid phone number';
            //     }
            //     console.log('telefono seleccionado:', phoneNumber);
            // } catch (e) {
            //     this.validacion.telefono.invalido = true;
            //     this.validacion.telefono.texto = 'Invalid phone number';
            // }

        },
        handleCountryChange(countryData) {
            const currentDialCode = this.previousCountry ? this.previousCountry.dialCode : '';
            const newDialCode = countryData.dialCode;

            if (currentDialCode == '' && this.user.telefono.startsWith('+')) {
                this.previousCountry =  countryData.dialCode;
            } else {
                // Extraer el número sin el código de país anterior
                const phoneNumberWithoutCountryCode = this.phoneNumber.replace('+' + currentDialCode, '').trim();

                // Actualizar el teléfono con el nuevo código de país
                this.user.telefono = '+' + newDialCode + phoneNumberWithoutCountryCode;
                this.phoneNumber = this.user.telefono;
                this.previousCountry = newDialCode;
            }
        },
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
                this.formDirty = false;
                this.$parent.$refs.login.user.nombres = this.user.nombre
            }).catch((error) => {
                this.validar_data()
            });
        },

        // submitFile: function () {
        //     this.guardo = false;
        //     this.photo = this.$refs.photo.files[0];
        //     this.nombre_photo = this.$refs.photo.files[0].name;
        //     const formData = new FormData();
        //     formData.append('photo', this.photo);
        //     const headers = { 'Content-Type': 'multipart/form-data' };
        //     axios.post('/ajax/usuario/perfil/cambiar_photo', formData, { headers }).then(response => {
        //         this.guardo = true;
        //         this.formDirty = false;
        //     }).catch((error) => {
        //     });
        // },
        updatePhoto: function (photo) {
            this.user.photo = photo;
            this.openPhotoEdit = false;
        },
        selectPhoto: function () {
            this.openPhotoEdit = !this.openPhotoEdit;
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
                this.formDirty = true;
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
            axios.get('/ajax/usuario/validar/update', { params: data })
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
                title: this._i18n.t('frontend.delete_account'),
                message: this._i18n.t('frontend.delete_account_message'),
                useConfirmBtn: true,
                isShown: true,
                disableOverlayClick: true,
                customClass: 'confirmar',
                customCloseBtnText: this._i18n.t('frontend.go_back'), //string -- close button text
                customCloseBtnClass: 'btn btn-secondary', //string -- custom class for close button
                customConfirmBtnText: this._i18n.t('frontend.delete_account_confirm_button'), //string -- confirm button text
                customConfirmBtnClass: 'btn btn-danger', //string -- custom class for confirm button
                onConfirm: function () {
                    axios.delete('/ajax/usuario').then(response => {
                        window.location.href = '/';
                    })
                }
            })
        },
        removeUndefinedText() {
      const telInput = this.$refs.telInput.$el;
      const selectionSpan = telInput.querySelector('.vti__selection');
      if (selectionSpan) {
        selectionSpan.childNodes.forEach(node => {
          if (node.nodeType === Node.TEXT_NODE && (node.nodeValue.trim() === 'undefined' || node.nodeValue.trim() === '')) {
            node.nodeValue = '';
          }
        });
      }
    }

    },
    computed: {
        loginSocial: function () {
            return $.inArray(this.user.facebook_id, ['', null]) === -1 ||
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
