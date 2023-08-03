<template>
    <div>
        <!-- Modal begin-->
        <div class="modal fade" id="login-modal" role="dialog" aria-labelledby="exampleModalCenterTitle"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"
                                    style="opacity: 1; margin-top: -1em">
                                <span aria-hidden="true" class="cerrar">{{ $t('frontend.close') }} &times;</span>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-12">

                                
                                <div class="row pl-3 pr-3 m-2 justify-center">
                                    <div class="col-md-12">

                                        <h3 class="text-center mt-2 ml-1" style="font-size: 0.7em;" >{{ $t('frontend.not_a_volunteer') }}</h3>
                                        <a class="btn btn-primary tag-social facebook py-3" @click="registro_facebook()" style="width: inherit"><i
                                                class="fab fa-facebook-f"></i>&nbsp;&nbsp;facebook</a>
                                        <a class="btn btn-primary tag-social google py-3" @click="registro_google()" style="width: inherit"><i
                                                class="fab fa-google"></i>&nbsp;&nbsp;google</a>
                                    </div>
                                </div>
                                <hr style="border-bottom:solid 1px grey; width:60%; ">
                                <div class="row pl-3 pr-3 m-2">
                                    <div class="col-md-12">
                                        <form id="frmLogin">
                                            <div v-if="showMailLogin">
                                                <div class="form-group" style="margin-bottom: 0.5em;">
                                                    <label for="mail">{{ $t('frontend.mail') }}</label>
                                                    <input v-bind:class="{ 'is-invalid': hasError }"
                                                        type="email"
                                                        v-model="credentials.mail"
                                                        v-on:keyup.enter="login"
                                                        class="form-control"
                                                        id="mail"
                                                        v-bind:placeholder="$t('frontend.mail_placeholder')"
                                                        style="font-size: 0.5em;"
                                                        required>
                                                </div>
                                                <div class="form-group" style="margin-bottom: 0.5em;">
                                                    <label for="password">{{ $t('frontend.password') }}</label>
                                                    <input type="password"
                                                        v-bind:class="{ 'is-invalid': hasError }"
                                                        v-model="credentials.password"
                                                        v-on:keyup.enter="login"
                                                        class="form-control"
                                                        id="password"
                                                        v-bind:placeholder="$t('frontend.password_placeholder')"
                                                        style="font-size: 0.5em;"
                                                        required>
                                                </div>
                                                <div class="row">
                                                    <div class="col">
                                                        <p class="text-danger">{{ mensajeError }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row h-100 align-items-center">
                                                
                                                <div class="col-6 text-left" style="font-size:0.5em" >
                                                    <a v-if="showMailLogin" href="/password/reset" class="forget">{{ $t('frontend.forget_password') }}</a>
                                                    <a v-if="showMailLogin" href="/registro" class="forget" style="font-size:0.8em">
                                                        {{ $t('frontend.volunteer_me') }}
                                                    </a>
                                                </div>
                                                <div class="col-6  text-right">
                                                    <button
                                                            v-if="showMailLogin"
                                                            id="btnLogin"
                                                            v-on:click="login"
                                                            type="button"
                                                            class="btn btn-primary"
                                                    >
                                                        {{ $t('frontend.login') }}
                                                    </button>
                                                    <button v-if="!showMailLogin" 
                                                            v-on:click="showMailLogin = true"
                                                            class="forget"
                                                            style="font-size:0.5em">
                                                        {{ $t('frontend.use_mail_login') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal End-->

        <nav class="navbar navbar-expand-md navbar-dark bg-techo-blue">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <a class="navbar-brand" href="/">
                            <img class="techo-logo" src="/img/techo-logo_269x83.png" alt="Techo">
                        </a> 
                    </div>
                </div>
                
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                        aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse text-right" id="navbarCollapse">
                    <div class="btn-group " role="group" ref="paises" v-if="this.showpaises==0">
                        <button type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-secondary dropdown-toggle" v-for="(p, i) in paises" v-if="p.id == pais" v-text="paises[i].nombre" style="text-transform: uppercase; font-weight: bold"></button>
                        <div class="dropdown-menu">
                            <button v-for="p in paises" v-text="p.nombre" class="dropdown-item" type="button" v-on:click="ir_a_pais(p.id)"></button>
                        </div>
                    </div>
                        <br>
                    <!-- <a class="btnUser d-inline d-md-none" v-on:click="perfil" v-if="authenticated">{{ $t('frontend.hello') }}, {{ user.nombres }}</a> -->
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item active d-block d-md-none" v-if="authenticated">
                            <a class="nav-link text-uppercase" v-on:click="misactividades">{{ $t('frontend.my_activities') }}</a>
                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated">
                            <a class="nav-link text-uppercase" v-on:click="evaluacion">{{ $t('frontend.my_score') }}</a>
                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated">
                            <a class="nav-link text-uppercase" v-on:click="perfil">{{ $t('frontend.profile') }}</a>
                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated && this.verAdmin">
                            <a class="nav-link text-uppercase" v-on:click="admin">{{ $t('frontend.admin') }}</a>
                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated && this.docs">
                            <a class="nav-link text-uppercase" v-on:click="ayuda">{{ $t('frontend.help') }}</a>
                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated">
                            <a class="nav-link text-uppercase" v-on:click="logout">{{ $t('frontend.logout') }}</a>
                        </li>

                        <li class="nav-item active d-block d-md-none locale-changer " v-show="langs.length>0" >
                            <select v-model="_i18n.locale" class="btnUser nav-item active d-block d-md-none" @change="onChangeLocalization($event)">
                                <option class="btn dropdown-item btnUser dropdown-toggle"  v-for="(lang, i) in langs" :key="`Lang${i}`"  :value="lang[0]">
                                    {{ lang[1] }}
                                </option>
                            </select>
                        </li>
                    </ul>
                </div>


                <div class="locale-changer col-md-1 offset-md-2 d-none d-md-block" >
                    <select v-if="langs.length>0" v-model="_i18n.locale" class="btn dropdown-toggle btn-secondary btnUser" @change="onChangeLocalization($event)">
                        <option class="dropdown-item" v-for="(lang, i) in langs" :key="`Lang${i}`"  :value="lang[0]">
                            {{ lang[1] }}
                        </option>
                    </select>
                </div>

                <!-- Si esta autenticado -->
                <div id="userDetail" class="col-md-2 d-none d-md-block" v-if="authenticated">
                    <div class="btn-group" role="group">
                        <button
                                type="button"
                                class="btn btn-secondary btnUser dropdown-toggle"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                        >
                               {{ $t('frontend.hello') }}, {{ user.nombres }}
                        </button>
                        <div
                                class="dropdown-menu"
                                aria-labelledby="btnUser"
                        >
                            <button
                                    class="dropdown-item"
                                    id="btn-mis-actividades"
                                    type="button"
                                    v-on:click="misactividades"
                            >
                                {{ $t('frontend.my_activities') }}</button>
                            <button
                                    class="dropdown-item"
                                    id="btn-evaluacion"
                                    type="button"
                                    v-on:click="evaluacion"
                            >
                                {{ $t('frontend.my_score') }}
                            </button>
                            <button
                                    class="dropdown-item"
                                    id="btn-perfil"
                                    type="button"
                                    v-on:click="perfil"
                            >
                                {{ $t('frontend.profile') }}
                            </button>
                            <button
                                    v-show="this.verAdmin"
                                    class="dropdown-item"
                                    id="btn-admin" type="button"
                                    v-on:click="admin"
                            >
                                {{ $t('frontend.admin') }}
                            </button>
                            <button
                                    v-if="authenticated && this.docs"
                                    class="dropdown-item"
                                    id="btnLogout"
                                    type="button"
                                    v-on:click="ayuda"
                            >
                                {{ $t('frontend.help') }}
                            </button>
                            <button
                                    class="dropdown-item"
                                    id="btnLogout"
                                    type="button"
                                    v-on:click="logout"
                            >
                                {{ $t('frontend.logout') }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Si no esta autenticado -->
                <form class="form-inline" v-else>
                    <button
                            class="btn btn-primary"
                            type="button"
                            data-toggle="modal"
                            data-target="#login-modal"
                            id="btnShowModal"
                    >
                        {{ $t('frontend.login_or_register') }}
                    </button>
                </form>
            </div>
        </nav>
    </div>
</template>

<script>
    export default {
        name: "login",
        props:['usuario', 'veradmin', 'showlogin', 'docs', 'available_locales', 'pais', 'showpaises' ],
        data () {
            let data = {
                credentials: {
                    mail: '',
                    password: '',
                },
                hasError: false,
                mensajeError: "",
                authenticated: false,
                user: {
                    nombres: '',
                    id: ''
                },
                verAdmin: this.veradmin,
                paises: [],
                showMailLogin: false,
            };
            
            if(this.usuario) {
                let user = JSON.parse(this.usuario);
                data.user.nombres = user.nombres;
                data.user.id = user.idPersona;
            }
            return data
        },
        created () {
          this.authenticated = this.checkLogin();
        },
        computed: {
            langs: function() {
                if(this.available_locales) {
                    let locales = this.available_locales.split(',');
                    return locales.map(function(v){ return v.trim().split('|') });
                }
                else return []
            },
        },
        mounted(){
            if(this.showlogin){
                $('#btnShowModal').trigger('click');
            }
            this.paises_habilitados();
            //Eventos
            events.$on('cerrar-sesion', this.logout);

        },
        methods: {
            ir_a_pais: function(codigo) {
                //console.log('pais');
                window.location.href = '/seleccionar-pais/' +  codigo;
            },
            paises_habilitados: function() {
                axios.get('/ajax/paises/habilitados')
                    .then(respuesta => { this.paises = respuesta.data; })
                    .catch(error => { debugger; });
            },
            registro_facebook: function() {
              window.location.href = '/auth/facebook';
            },
            registro_google: function() {
              window.location.href = '/auth/google';
            },
            perfil: function() {
              window.location.href = '/perfil';
            },
            admin: function() {
              window.location.href = '/admin/actividades/usuario';
            },
            ayuda: function() {
              window.location.href = this.docs;  
            },
            misactividades: function() {
              window.location.href = '/perfil/actividades';
            },
            evaluacion: function() {
              window.location.href = '/perfil/evaluacion';
            },
            login: function () {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                if(this.credentials.mail === "" || this.credentials.password === ""){
                    this.hasError = true;
                    this.mensajeError = this._i18n.t('frontend.login_error');
                    return
                }
                axios.post(
                    '/login',
                    {
                        mail: this.credentials.mail,
                        password: this.credentials.password
                    })
                    .then(response => {
                        // console.log(response);
                        $('#login-modal').modal('hide');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                        let permisos = response.data.permisos.map(function (permiso) {
                            return permiso.name;
                        });

                        if (permisos.find(function(permiso){
                            return permiso === "ver_backoffice";
                        }) !== undefined){
                            this.verAdmin = true;
                        }
                        this.showValidUser(response.data.user);
                        if(response.data.after_login !== ''){
                            window.location = response.data.after_login; //redirect
                        }
                        else {
                            window.location.reload();   
                        }
                    })
                    .catch((error) => {
                        this.hasError = true;
                        if (error.response) {
                            this.mensajeError = error.response.data.message;
                            console.log(error.response.data);
                            console.log(error.response.status);
                            console.log(error.response.headers);
                        } else if (error.request) {
                            console.log(error.request);
                        } else {
                            console.log('Error', error.message);
                            this.mensajeError = error.message;
                        }
                        console.log(error.config);
                    });
            },
            logout: function() {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                axios.post(
                    '/logout')
                    .then(response => {
                        this.authenticated = false;
                        window.location = '/';
                    })
                    .catch((error) => {
                        this.hasError = true;
                        if (error.response) {
                            console.log(error.response.data);
                            console.log(error.response.status);
                            console.log(error.response.headers);
                        } else if (error.request) {
                            console.log(error.request);
                        } else {
                            console.log('Error', error.message);
                        }
                        console.log(error.config);
                    });

            },
            showValidUser: function(user) {
                this.authenticated = true;
                this.user.nombres = user.nombres;
                this.user.id = user.idPersona;
                let event = new CustomEvent('loggedIn');
                window.dispatchEvent(event);
            },
            checkLogin() {
                if (this.user.nombres) return true;
                return false;
            },
            onChangeLocalization(event) {
                window.location.href = '/locale/' + event.target.value;
            }
        }
    }
</script>

<style scoped>
    .techo-btn-blanco {
     border: none;
     color: #0092dd;
     background-color: #ffffff;
     font-size: 1rem;
    }

    .tag-social {
        line-height: 1.33;
        font-size: 12px;
        width: 269px;
    }

    .registro {
        background-color: #0092dd;
        font-weight: bold;
        text-align: center;
        color: #ffffff;
    }

    @media (max-width: 768px) {
        .registro {
            margin-left: 15px;
            margin-right: 15px;
            padding-top: 10%;
            padding-bottom: 10%;
        }
        .tag-social {
            width:  fit-content;
            font-size: .75rem;
        }
    }

    .registro h1{
        vertical-align: middle;
        font-family: Montserrat, sans-serif;
        font-size: 2rem;
    }

    .dropdown-item {
        font-family: Montserrat, sans-serif;
        text-transform: uppercase;
        font-weight: bold;
        font-size: 12px;
        font-style: normal;
        font-stretch: normal;
        line-height: normal;
        letter-spacing: normal;
        text-align: left;
        color: #0092dd;
    }
    .dropdown-item:focus {
        outline:0;
    }
    .dropdown-item:hover {
        background-color: #007bff;
        color: #fff;
    }
    .dropdown-item:active {
        background-color: #007bff;
        color: #fff;
    }
    .btnUser {
        border: none;
        background: #0092dd;
        text-transform: capitalize;
        color: #fff !important;
    }
    .dropdown-menu {
        padding: 1em 0;
        margin: 0;
    }
    a.google {
        border: none;
    }
    a.facebook {
        border:none;
    }

</style>
