
<template>
    <span>
        <!-- Modal begin-->
        <div class="modal fade" id="login-modal" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body" style="padding: 0">
                        <div class="">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar" style="opacity: 1; margin-top: -1em">
                                <span aria-hidden="true" class="cerrar">Cerrar &times;</span>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-6"  style="padding: 1em 2em">
                                <h2>Ingresar a tu perfil</h2>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <a class="btn btn-primary facebook" @click="registro_facebook()"><i class="fab fa-facebook-f"></i>&nbsp;&nbsp;LOGIN CON FACEBOOK</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <a class="btn btn-primary google form-control" @click="registro_google()"><i class="fab fa-google"></i>&nbsp;&nbsp;LOGIN CON GOOGLE</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"  style="padding: 1em 2em">
                                <img src="/img/techo-cyan_235x62.png" alt="Ingresa a tu cuenta de Techo" height="25" width="95">
                                <form id="frmLogin">
                                    <div class="form-group">
                                        <label for="mail">Correo Electrónico</label>
                                        <input v-bind:class="{ 'is-invalid': hasError }" type="email" v-model="credentials.mail" class="form-control" id="mail" placeholder="Tu dirección de email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" v-bind:class="{ 'is-invalid': hasError }" v-model="credentials.password" class="form-control" id="password" placeholder="Password" required>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="remember">
                                        <label class="form-check-label" for="remember">Recordarme en este equipo</label>
                                    </div>
                                    <button id="btnLogin" v-on:click="login" type="button" class="btn techo-btn-azul" style="margin-top: 2em">Ingresar</button>
                                    <br>
                                    <a href="#">Olvidé mi contraseña</a>
                                    <a href="/registro">Me quiero registrar</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal End-->
    <!-- Si esta autenticado -->
        <span id="userDetail" v-if="authenticated">
            <div class="btn-group techo-btn-blanco" role="group" >
                <button
                    id="btnUser"
                    type="button"
                    class="btn btn-secondary dropdown-toggle techo-btn-blanco"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                    v-model="user.nombres"
                >
                        {{ user.nombres }}
                </button>
                <div
                    class="dropdown-menu"
                    aria-labelledby="btnUser"
                >
                    <button class="dropdown-item" id="btnLogout" type="button" v-on:click="logout">Salir</button>
                </div>
            </div>
        </span>

    <!-- Si no esta autenticado -->

        <form class="form-inline mt-2 mt-md-0" v-else>
            <button
                    class="btn my-2 my-sm-0 techo-btn-blanco"
                    type="button"
                    data-toggle="modal"
                    data-target="#login-modal"
                    id="btnShowModal"
            >
                Ingresar
            </button>
        </form>



    </span>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "login",
        data () {
            return {
                credentials: {
                    mail: '',
                    password: '',
                },
                hasError: false,
                authenticated: false,
                user: {
                    nombres: '',
                    id: ''
                }
            }
        },
        created () {
          this.authenticated = this.checkLogin();
          this.user.nombres = this.getCookie('user.nombres');
          // console.log('nombres: ' + this.user.nombres);
        },
        methods: {
            registro_facebook: function() {
              window.location.href = '/auth/facebook';
            },
            registro_google: function() {
              window.location.href = '/auth/google';
            },

            login: function () {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
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

                        this.authenticated = true;
                        this.setCookie('user.nombres', response.data.user.nombres, 1);
                        this.user.nombres = this.getCookie('user.nombres');
                        this.user.id = this.getCookie('user.idPersona');
                        var event = new CustomEvent('loggedIn');
                        window.dispatchEvent(event);
                    })
                    .catch((error) => {
                        // Error
                        this.hasError = true;
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
            },

            logout: function() {
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                axios.post(
                    '/logout')
                    .then(response => {
                        this.setCookie('user.nombres', '', 0);
                        this.authenticated = false;
                        window.location = '/';
                    })
                    .catch((error) => {
                        // Error
                        this.hasError = true;
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

            },

            setCookie(cname, cvalue, exdays) {
                let d = new Date();
                d.setTime(d.getTime() + (exdays*24*60*60*1000));
                let expires = "expires="+ d.toUTCString();
                document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
            },

            getCookie(cname) {
                let name = cname + "=";
                let decodedCookie = decodeURIComponent(document.cookie);
                let ca = decodedCookie.split(';');
                for(let i = 0; i <ca.length; i++) {
                    let c = ca[i];
                    while (c.charAt(0) == ' ') {
                        c = c.substring(1);
                    }
                    if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                    }
                }
                return "";
            },

            checkLogin() {
        		var self = this;
                        let username = this.getCookie("user.nombres");
                        if (username != "") {
                            return true;
                        }
        		axios.get('/ajax/usuario').then(response => {
        			if(!response.data) return false;
                                self.authenticated = true;
                                self.setCookie('user.nombres', response.data.nombres, 1);
                                self.user.nombres = self.getCookie('user.nombres');
                                self.user.id = self.getCookie('user.idpersona');
                                var event = new CustomEvent('loggedin');
                                window.dispatchEvent(event);
        			console.log(response);
        		});
                return false;
            }
        }
    }
</script>

<style scoped>
 .techo-btn-blanco {
     margin-right: 4em;
     border: none;
     background-color: #0092dd;
     color: #ffffff;
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

 .dropdown-menu {
        width: 10em;
        padding: 1em;
        margin: 0;
    }


</style>
