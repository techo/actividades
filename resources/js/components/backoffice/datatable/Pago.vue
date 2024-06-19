<template>
    <span>
        <v-switch
                v-model="pago"
                theme="bootstrap"
                color="primary"
                id="pago"
                name="pago"
                type-bold="true"
                text-enabled="$t('backend.paid')"
                text-disabled="$t('backend.pending')"
        >
        </v-switch>
        <i class="fa fa-exclamation text-danger" v-show="errorIcon"></i>
        <i class="fa fa-exclamation text-warning" v-show="rowData.voucherUrl&&!pago"></i>
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
            'rowData': function () {
                this.pago = (this.rowData.pago === null) ? 0 : this.rowData.pago;
            }

        }
    }
</script>

<style scoped>

</style>