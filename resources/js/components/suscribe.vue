<template>
    <span>
        <p class="m-3">
            {{ $t('frontend.empty_search') }}
        </p>
        <!-- <p class="mt-3 lead">
            {{ $t('frontend.suscribe_so_we_get_in_touch') }}
        </p>
        <input v-show="!submited" class="mt-3 text-center" type="text" name="email" id="email" :placeholder="$t('frontend.email')" v-model="data.mail"
            @blur="validateEmail">
        <p v-show="submited" class="lead text-success">{{ $t('frontend.suscribe_thanks') }}</p>
        <small class="form-text text-danger" v-if="!validation">{{ $t('frontend.email_validation_error') }}</small>
        <div v-show="loading" class="loading" style="text-align: center">
            <i class="fas fa-sync fa-spin fa-3x"></i>
        </div>
        <div class="row justify-content-center text-light">
            <a class="btn btn-primary m-3" @click="suscribe" v-show="!submited">
                <strong>{{ $t('frontend.suscribe') }} </strong>
            </a>
        </div> -->
        <a 
            class="btn btn-secondary"  
            href="autotest">
            {{ $t('frontend.autotestTituloBoton') }} 
        </a>

    </span>
</template>

<script>
import axios from 'axios';

export default {
    name: "suscribe",

    data() {
        return {
            data: {
                mail: '',
                filtros_categorias: [],
                filtros_ubicaciones: [],
            },
            validation: true,
            loading: false,
            submited: false,
            url: '/ajax/actividades/suscribe',

        }
    },
    props: [
        'filtros'
    ],
    methods: {
        validateEmail() {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.data.mail)) {
                this.validation = true;
            } else {
                this.validation = false;
            }
            console.log(this.msg)
        },
        suscribe() {
            this.validateEmail();
            if (this.loading || !this.validation) { return; };
            this.loading = true;

            this.data.filtros_categorias = this.filtros.tipos;
            this.data.filtros_ubicaciones = this.filtros.provincias;

            axios.post(this.url, this.data)
                .then(response => {
                    console.log(response);
                    this.submited=true;
                })
                .catch((error) => {
                    // Error
                    console.error('error en contenedor de tarjetas');
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

            this.loading = false;
        },

    },
}
</script>

<style scoped>
</style>