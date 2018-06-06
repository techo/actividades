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
                            :options="condiciones"
                            label="condicion"
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
        props: ['campos'],
        data() {
            return {
                dataCampos: [],
                campoSeleccionado: "",
                condiciones: [
                    'mayor que',
                    'mayor o igual que',
                    'menor que',
                    'menor o igual que',
                    'igual a',
                    'distinto de',
                    'contiene',
                    'está en lista'
                ],
                condicionSeleccionada: "",
                valorCondicion: ""
            }
        },
        created(){
            this.dataCampos = JSON.parse(this.campos);
        },
        methods: {
            agregar: function () {
                let condicion = {
                  'campo': this.campoSeleccionado,
                  'condicion': this.condicionSeleccionada,
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