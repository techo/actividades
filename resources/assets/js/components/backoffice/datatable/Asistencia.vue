<template>
    <span>
        <v-switch
                v-model="presente"
                theme="bootstrap"
                color="primary"
                id="pago_"
                name="pago"
                type-bold="true"
                text-enabled="Presente"
                text-disabled="Ausente"
        >
        </v-switch>
        <i class="fa fa-exclamation text-danger" v-show="errorIcon"></i>
    </span>
</template>

<script>
    import vSwitch from 'vue-switches';
    import axios from 'axios';

    export default {
        name: "asistencia",
        components: {'v-switch': vSwitch},
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                type: Number
            }
        },
        data() {
            return {
                presente: this.rowData.presente,
                idInscripcion: this.rowData.id,
                errorIcon: false,
            }
        },
        created() {
        },
        computed: {},
        methods: {
            actualizar() {
                this.errorIcon = false;
                let url = '/admin/ajax/actividades/' + this.rowData.idActividad + '/inscripciones/' + this.idInscripcion;
                let params = {
                    'presente': this.presente,
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
        },
        watch: {
            presente() {
                this.actualizar();
            },
            'rowData.presente': function () {
                this.presente = (this.rowData.presente === null) ? 0 : this.rowData.presente;
            }
        }
    }
</script>

<style scoped>

</style>