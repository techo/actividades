<template>
    <div>
        <!-- informacion general -->
        <div class="box">
            <div class="row text-center">
                   
                </div>
                <div class="box-header with-border bg-success text-white">
                <h2 class="box-title">{{ $t('backend.confirm') }} {{ $t('backend.present') }}: {{  inscripto.nombres }} {{  inscripto.apellidoPaterno }}</h2>
               
            </div>
            <div class="box-body">
                <div class="row text-center mb-4">
                    <div v-if="inscripto.photo" class="col-12 col-md-4 mb-3">
                        <img class="imagen-miniatura-redonda" :src="'/'+inscripto.photo" alt="Foto">
                    <a :href="'/admin/usuarios/'+inscripto.idPersona" class="btn btn-primary btn-xs" target="_blank" >
                       <i class="fa fa-user" ></i> &nbsp; {{ $t('backend.view_profile') }}
                    </a>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <h5>{{ $t('backend.email') }}: {{ inscripto.mail }}</h5>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <h5>{{ $t('backend.phone') }}: {{ inscripto.telefonoMovil }}</h5>
                    </div>
                </div>
                <div class="row text-center mt-4">
                    <button
                        v-if="!inscripcionConfirmada"
                        @click="confirmParticipation"
                        class="btn btn-success me-2"
                    >
                        {{ $t('backend.confirm') }}
                    </button>
                    <p v-else class="text-success">
                        {{ $t('backend.confirmed') }}
                    </p>
                </div>

            </div>
        </div>
    

    </div>
</template>

<script>

    export default {
        name: "confirmar-presente",
        props: ['inscripcion','persona'],
        components: {  },
        data() {
            return {
                inscripto: {},
                inscripcionBD: {},
                inscripcionConfirmada: false,
            }
        },
        created() {
        },
        mounted() {
            
            this.inscripto = JSON.parse(this.persona);
            this.inscripcionBD = JSON.parse(this.inscripcion);

            if(this.inscripcionBD.presente)
                this.inscripcionConfirmada = true;
        },
        computed: {
        },
        filters: {},
        watch: {
        },
        methods: {
            confirmParticipation() {
                this.errorIcon = false;
                let url = '/admin/ajax/actividades/' +  this.inscripcionBD.idActividad + '/inscripciones/' + this.inscripcionBD.idInscripcion;
                let params = {
                    'presente': true,
                };
                this.axiosPost(url,
                    function (data, self) {
                        self.errorIcon = false;
                        Event.$emit('asistencia:cambio');
                    }, params);
            },
            axiosPost(url, fCallback, params = []) {
                axios.post(url, params)
                    .then(response => {
                        fCallback(response.data, this)
                        this.inscripcionConfirmada = true;
                    })
                    .catch((error) => {
                        // Error
                        this.errorIcon = true;
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

</style>