<template>
    <div>
        <h2>Filtros</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="campo">Campo</label>
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
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="condicion">Condición</label>
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
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="valor">Valor</label>
                    <input type="text" name="valor" id="valor" class="form-control" v-model="valorCondicion" placeholder="Escriba un valor">
                </div>
            </div>
            <div class="col-md-3">
                <br>
                <button class="btn btn-primary" @click="this.agregar">Agregar condición</button>
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
                valorCondicion: ""
            }
        },
        created(){
            this.dataCampos = JSON.parse(this.campos);
            this.dataCondiciones = JSON.parse(this.condiciones);
        },
        methods: {
            agregar: function () {
                let condicion = {
                  'campo': this.campoSeleccionado.id,
                  'campoLabel': this.campoSeleccionado.campo,
                  'condicion': this.condicionSeleccionada.value,
                  'condicionLabel': this.condicionSeleccionada.label,
                  'valor': this.valorCondicion
                };
                Event.$emit('agregar-condicion', condicion);
                this.campoSeleccionado = this.condicionSeleccionada = this.valorCondicion = "";
            }
        }
    }
</script>

<style scoped>

</style>