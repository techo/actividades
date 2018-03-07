
<template>
    <span>
        <form class="form-inline mt-2 mt-md-0">
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

        <!-- Modal begin-->
        <div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body" style="padding: 0">
                        <div class="">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="opacity: 1; margin-top: -1em">
                                <span aria-hidden="true" class="cerrar">Cerrar &times;</span>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-6"  style="padding: 1em 2em">
                                <img src="/img/techo-cyan_235x62.png" alt="Ingresa a tu cuenta de Techo" height="25" width="95">
                                <h2>Ingresar a tu perfil</h2>
                                <form id="frmLogin">
                                    <div class="form-group">
                                        <label for="mail">Correo Electrónico</label>
                                        <input type="email" v-model="credentials.mail" class="form-control" id="mail" placeholder="Tu dirección de email" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" v-model="credentials.password" class="form-control" id="password" placeholder="Password" required>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="remember">
                                        <label class="form-check-label" for="remember">Recordarme en este equipo</label>
                                    </div>
                                    <button id="btnLogin" v-on:click="login" type="button" class="btn techo-btn-azul" style="margin-top: 2em">Ingresar</button>
                                    <br>
                                    <a href="#">Olvidé mi contraseña</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Modal End-->

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
                    password: ''
                }
            }
        },
        methods: {
            login: function () {
                console.log('!');
                axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                axios.post(
                    'http://atlas.test/login',
                    {
                        mail: this.credentials.mail,
                        password: this.credentials.password
                    })
                    .then(response => {
                        console.log(response);
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