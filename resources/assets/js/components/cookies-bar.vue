<template>
    <div id="cookies-bar">
        Este sitio utiliza cookies para brindar una correcta experiencia al usuario. Si usas este sitio, estás aceptando las
        <a href="https://www.techo.org/politica-de-privacidad#cookies" target="_blank">Politicas de Cookies</a>
        <span class="pull-right" @click="cerrar">
           <i class="fas fa-times"></i>
       </span>
    </div>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "cookies-bar",
        methods: {
            cerrar: function () {
                let url = "/cookie/close";
                this.axiosGet(
                    url,
                    function (data, self) {
                        $('#cookies-bar').slideUp("slow");
                    }
                );
            },
            axiosGet(url, fCallback, params = [], fError = function(){}) {
                axios.get(url, params)
                    .then(response => {
                        fCallback(response.data, this)
                    })
                    .catch((error) => {
                        // Error
                        fError(error, this); //handler personalizado
                        console.error('Error en: ' + url);
                        if (error.response) {
                            console.error(error.response.data);
                            console.error(error.response.status);
                            console.error(error.response.headers);
                        } else if (error.request) {
                            console.error(error.request);
                        } else {
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });

            },
        }
    }
</script>

<style scoped>
    div {
        background-color: black;
        color: #eeeeee;
        padding: 10px 5px;
    }

    span {
        padding-right: 2%;
        cursor: pointer;
    }
</style>