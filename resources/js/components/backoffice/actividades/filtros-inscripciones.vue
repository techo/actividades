<template>
    <div>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="campo">{{ $t('backend.field') }}</label>
                    <v-select
                            :options="dataCampos"
                            label="campo"
                            placeholder="Seleccione"
                            name="campo"
                            id="campo"
                            v-model="campoSeleccionado"
                            :filterable=true
                    >
                    </v-select>
                    <p class="text-danger" v-show="errorCampo"><small>{{ $t('backend.this_field_is_required') }}</small></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group" v-show="showCondiciones">
                    <label for="condicion">{{ $t('backend.condition') }}</label>
                    <v-select
                            :options="dataCondiciones"
                            label="label"
                            placeholder="Seleccione"
                            name="condicion"
                            id="condicion"
                            v-model="condicionSeleccionada"
                            :filterable=true
                    >
                    </v-select>
                    <p class="text-danger" v-show="errorCondicion"><small>{{ $t('backend.this_field_is_required') }}</small></p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group" v-show="showCondiciones">
                    <label for="valor">{{ $t('backend.value') }}</label>
                    <input type="text" name="valor" id="valor" class="form-control" v-model="valorCondicion" placeholder="Escriba un valor">
                </div>
            </div>
            <div class="col-md-3">
                <br>
                <button class="btn btn-primary" @click="this.agregar">{{ $t('backend.add_condition') }}</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "filtros-inscripciones",
        props: ['campos', 'condiciones'],
        data() {
            return {
                dataCampos: [],
                campoSeleccionado: "",
                dataCondiciones: [],
                condicionSeleccionada: "",
                valorCondicion: "",
                validacion: true,
                showCondiciones: false
            }
        },
        watch: {
            campoSeleccionado: function (nuevo, viejo) {
                console.log(nuevo);
                if(!nuevo.condiciones){
                    this.condicionSeleccionada = {label:"contiene", value:"like"};
                    this.valorCondicion = 1;
                    this.showCondiciones = false;
                } else {
                    this.condicionSeleccionada = "";
                    this.valorCondicion = "";
                    this.showCondiciones = true;
                }
            },
        },
        created(){
            this.dataCampos = JSON.parse(this.campos);
            this.dataCondiciones = JSON.parse(this.condiciones);
            this.condicionSeleccionada = {label:"contiene", value:"like"};
        },
        computed: {
            errorCampo: function () {
                return (!this.validacion && this.campoSeleccionado === "");
            },
            errorCondicion: function () {
                return (!this.validacion && this.condicionSeleccionada === "");
            }
        },
        methods: {
            agregar: function () {
                if(this.campoSeleccionado !== "" && this.condicionSeleccionada !== ""){
                    let condicion = {
                        'campo': this.campoSeleccionado.id,
                        'campoLabel': this.campoSeleccionado.campo,
                        'condicion': this.condicionSeleccionada.value,
                        'condicionLabel': this.condicionSeleccionada.label,
                        'valor': this.valorCondicion
                    };
                    Event.$emit('agregar-condicion', condicion);
                    this.campoSeleccionado = this.condicionSeleccionada = this.valorCondicion = "";
                    return this.validacion = true;
                }
                return this.validacion = false;

            }
        }
    }
</script>

<style scoped>

</style>