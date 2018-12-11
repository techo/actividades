<template>
    <span>
        <select name="estadoInscripcion" id="estadoInscripcion" v-model="estado">
            <option value="Confirmado">Confirmado</option>
            <option value="Sin Confirmar">Sin Confirmar</option>
            <option value="Sin Interés">Sin Interés</option>
            <option value="Sin Contactar">Sin Contactar</option>
            <option v-if="esConstruccion" value="Pre-Inscripto">Pre-inscripto</option>
        </select>
        <i class="fa fa-exclamation text-danger" v-show="errorIcon"></i>
    </span>
</template>

<script>
    import axios from 'axios';
    import store from '../stores/store';
    export default {
        name: "estadoInscripcion",
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
                estado: this.rowData.estado,
                errorIcon: false,
            }
        },
        computed: {
            esConstruccion: function () {
                return store.state.esConstruccion;
            }
        },
        methods: {
            actualizar() {
                this.errorIcon = false;
                let url = '/admin/ajax/actividades/' + this.rowData.idActividad + '/inscripciones/' + this.rowData.id;
                let params = {
                    'estado': this.estado
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
            estado() {
                this.actualizar();
            },
            'rowData': function () {
                this.estado = (this.rowData.estado === null) ? 0 : this.rowData.estado;
            }
        }
    }
</script>

<style scoped>

</style>