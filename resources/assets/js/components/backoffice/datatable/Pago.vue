<template>
    <span>
        <v-switch
                v-model="pago"
                theme="bootstrap"
                color="primary"
                id="pago"
                name="pago"
                type-bold="true"
                text-enabled="Pagado"
                text-disabled="Pendiente"
        >
        </v-switch>
        <i class="fa fa-exclamation text-danger" v-show="errorIcon"></i>
    </span>
</template>

<script>
    import vSwitch from 'vue-switches';
    import axios from 'axios';

    export default {
        name: "pago",
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
                pago: this.rowData.pago,
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
                    'pago': this.pago,
                };
                this.axiosPost(url,
                    function (data, self) {
                        self.errorIcon = false;
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
            pago() {
                this.actualizar();
            },
            // 'rowData.pago': function () {
            //     this.pago = (this.rowData.pago === null) ? 0 : this.rowData.pago;
            // }
            "this.rowData.pago": function (newData) {
                this.pago = (newData === null) ? 0 : newData;
            }

        }
    }
</script>

<style scoped>

</style>