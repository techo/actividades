<template>
    <div>
        <!-- Modal begin-->
        <div class="modal fade" id="login-modal" role="dialog" aria-labelledby="exampleModalCenterTitle"
             aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="">
                            <button type="button" class="close button-modal-close" data-dismiss="modal" aria-label="Cerrar"
                                    >
                                <span aria-hidden="true" class="cerrar">Cerrar &times;</span>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="row pl-sm-1 pl-md-4 m-3">
                                    <div class="col-md-12">
                                        <img src="/img/techo-cyan_235x62.png" alt="Ingresa a tu cuenta de Techo"
                                             height="25" width="95">
                                    </div>
                                </div>
                                <div class="row pl-sm-1 pl-md-4 m-3">
                                    <div class="col-md-12">
                                        <h2>Ingresar a tu perfil</h2>
                                    </div>
                                </div>
                                <div class="row pl-sm-1 pl-md-4 m-3">
                                    <div class="col-md-12">
                                        <a class="btn btn-primary facebook" @click="registro_facebook()"><i
                                                class="fab fa-facebook-f"></i>&nbsp;&nbsp;Ingresar con Facebook</a>
                                    </div>
                                </div>
                                <div class="row pl-sm-1 pl-md-4 m-3">
                                    <div class="col-md-12">
                                        <a class="btn btn-primary google" @click="registro_google()"><i
                                                class="fab fa-google"></i>&nbsp;&nbsp;Ingresar con Google</a>
                                    </div>
                                </div>
                                <div class="row pl-sm-1 pl-md-4 m-3">
                                    <div class="col-md-12">
                                        <form id="frmLogin">
                                            <div class="form-group">
                                                <label for="mail">Correo Electrónico</label>
                                                <input v-bind:class="{ 'is-invalid': hasError }"
                                                       type="email"
                                                       v-model="credentials.mail"
                                                       v-on:keyup.enter="login"
                                                       class="form-control"
                                                       id="mail"
                                                       placeholder="Ingresa tu correo electrónico"
                                                       required>
                                            </div>
                                            <div class="form-group">
                                                <label for="password">Contraseña</label>
                                                <input type="password"
                                                       v-bind:class="{ 'is-invalid': hasError }"
                                                       v-model="credentials.password"
                                                       v-on:keyup.enter="login"
                                                       class="form-control"
                                                       id="password"
                                                       placeholder="Ingresa tu contraseña"
                                                       required>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <p class="text-danger">{{ mensajeError }}</p>
                                                </div>
                                            </div>
                                            <div class="row h-100 align-items-center">
                                                <div class="col-4">
                                                    <button
                                                            id="btnLogin"
                                                            v-on:click="login"
                                                            type="button"
                                                            class="btn btn-primary"
                                                    >
                                                        Ingresar
                                                    </button>
                                                </div>
                                                <div class="col-8 text-right ">
                                                    <a href="/password/reset">Olvidé mi contraseña</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 registro">
                                <div class="row h-100 justify-content-center align-items-center">
                                    <div class="col">
                                        <h1 class="question-voluntary">¿TODAVÍA NO SOS VOLUNTARIO DE TECHO?</h1>
                                        <br>
                                        <a
                                                href="/registro"
                                                class="btn btn-light btn-lg my-sm-0 bg-white techo-btn-blanco"
                                        >
                                            ¡Quiero ser voluntario!
                                        </a>
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
                    <div class="col-md-4">
                        <a class="navbar-brand" href="/">
                            <img class="techo-logo" src="/img/techo-logo_269x83.png" alt="Techo">
                        </a>
                        <a class="btnUser d-inline d-md-none" v-on:click="perfil" v-if="authenticated">Hola, {{ user.nombres }}</a>
                    </div>
                </div>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
                        aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="col-md-4 collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item active">
                            <a class="nav-link text-uppercase" href="/actividades">Actividades <span class="sr-only">(current)</span></a>

                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated">
                            <a class="nav-link text-uppercase" v-on:click="misactividades">Mis Actividades</a>
                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated">
                            <a class="nav-link text-uppercase" v-on:click="perfil">Perfil</a>
                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated && this.verAdmin">
                            <a class="nav-link text-uppercase" v-on:click="admin">Admin</a>
                        </li>
                        <li class="nav-item active d-block d-md-none" v-if="authenticated">
                            <a class="nav-link text-uppercase" v-on:click="logout">Salir</a>
                        </li>
                    </ul>
                </div>
                <!-- Si esta autenticado -->
                <div id="userDetail" class="col-md-2 offset-md-4 d-none d-md-block" v-if="authenticated">
                    <div class="btn-group" role="group">
                        <button
                                type="button"
                                class="btn btn-secondary btnUser dropdown-toggle"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                                v-model="user.nombres"
                        >
                               Hola, {{ user.nombres }}
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
                                Mis Actividades</button>
                            <button
                                    class="dropdown-item"
                                    id="btn-perfil"
                                    type="button"
                                    v-on:click="perfil"
                            >
                                Perfil
                            </button>
                            <button
                                    v-show="this.verAdmin"
                                    class="dropdown-item"
                                    id="btn-admin" type="button"
                                    v-on:click="admin"
                            >
                                Admin
                            </button>
                            <button
                                    class="dropdown-item"
                                    id="btnLogout"
                                    type="button"
                                    v-on:click="logout"
                            >
                                Salir
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Si no esta autenticado -->
                <form class="form-inline col-md-2 offset-md-4 " v-else>
                    <button
                            class="btn btn-primary"
                            type="button"
                            data-toggle="modal"
                            data-target="#login-modal"
                            id="btnShowModal"
                    >
                        Ingresar
                    </button>
                </form>
            </div>
        </nav>
    </div>
</template>

<script>
    export default {
        name: "login",
        props:['usuario', 'veradmin', 'showlogin'],
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
                verAdmin: this.veradmin
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
        mounted(){
            if(this.showlogin){
                $('#btnShowModal').trigger('click');
            }
            //Eventos
            events.$on('cerrar-sesion', this.logout);

        },
        methods: {
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
            misactividades: function() {
              window.location.href = '/perfil/actividades';
            },
            login: function () {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                if(this.credentials.mail === "" || this.credentials.password === ""){
                    this.hasError = true;
                    this.mensajeError = "El Correo electrónico y la contraseña son requeridos";
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
            }
        }
    }
</script>

<style scoped>
    .techo-btn-blanco {
     border: none;
     color: #0092dd;
     background-color: #ffffff;
     font-size: 1.4rem;
    }

    .registro {
        background-color: #0092dd;
        font-weight: bold;
        text-align: center;
        color: #ffffff;
    }

    .button-modal-close {
        opacity: 1; 
        margin-top: -1em;
    }

    @media (max-width: 768px) {
        .registro {
            margin-left: 15px;
            margin-right: 15px;
            padding-top: 10%;
            padding-bottom: 10%;
        }

        .question-voluntary {
            font-size: 1.5em;
        }
    }

    .registro h1{
    vertical-align: middle;
     font-family: Montserrat, sans-serif;
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
        width: 10em;
        padding: 1em 0;
        margin: 0;
    }
    a.google {
        border: none;
    }
    a.facebook {
        border:none;
    }

    .button-modal-close {
        display: none;
    }

</style>
