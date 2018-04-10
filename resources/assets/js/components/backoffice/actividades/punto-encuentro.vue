<template>
    <span>
        <div class="row" v-show="!readonly">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="punto">Punto de Encuentro</label>
                    <input type="text" id="punto" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="horario">Horario</label>
                    <input type="text" id="horario" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="responsable">Responsable</label>
                    <input type="text" id="responsable" class="form-control">
                </div>
            </div>
            <div class="col-md-3">
                <a href="#">Guardar</a>
            </div>
        </div>
        <div class="row" v-for="punto in dataPuntosEncuentro">
            <div class="col-md-4">
                <div class="form-group">
                    <p>{{ punto.punto }}</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <p>{{ punto.horario }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                        <label for="responsable">Responsable</label>
                        <v-select
                                :options="dataCoordinadores"
                                label="localidad"
                                placeholder="Seleccione"
                                name="localidad"
                                id="localidad"
                                v-model="localidadSeleccionada"
                                v-bind:disabled="this.readonly"
                        >
                        </v-select>
                </div>
            </div>
            <div class="col-md-1">
                <div class="form-group"><br>
                    <p style="color: red">Borrar</p>
                </div>
            </div>
        </div>
    </span>
</template>

<script>
    export default {
        name: "punto-encuentro",
        props: ['readonly', 'puntos-encuentro', 'coordinadores'],
        data: function() {
            return {
                dataReadonly: this.readonly,
                dataPuntosEncuentro: this.puntosEncuentro,
                dataCoordinadores: JSON.parse(this.coordinadores)
            }
        }
    }
</script>

<style scoped>

</style>