<template>
    <span>
        <v-switch
                v-model="confirma"
                theme="bootstrap"
                color="primary"
                type-bold="true"
                :text-enabled="$t('backend.confirmed')"
                :text-disabled="$t('backend.unconfirmed')"
        >
        </v-switch>
        <i class="fa fa-exclamation text-danger" v-show="errorIcon"></i>
    </span>
</template>

<script>
    import vSwitch from 'vue-switches';
    import axios from 'axios';

    export default {
        name: "confirma",
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
                confirma: this.rowData.confirma,
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
                let url = '/admin/ajax/actividades/' + this.rowData.idActividad + '/inscripciones/' + this.rowData.id;
                let params = {
                    'confirma': this.confirma,
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
            confirma() {
                this.actualizar();
            },
            'rowData': function () {
                this.confirma = (this.rowData.confirma === null) ? 0 : this.rowData.confirma;
            }
        }
    }
</script>

<style scoped>

</style>