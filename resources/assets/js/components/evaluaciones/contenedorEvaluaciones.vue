<template>
    <span>
        <div v-for="persona in personas">
            <evaluar-persona prop-persona="persona" prop-actividad="actividad"></evaluar-persona>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="form-group" :class="{'has-error': !noInscripto}">
                    <label for="persona">Nombre </label>
                    <v-select
                            :options="listadoInscriptos"
                            label="nombre"
                            placeholder="Escribe el nombre, apellido o DNI"
                            name="persona"
                            id="persona"
                            v-model="persona"
                            :filterable=true
                    >
                    </v-select>
                </div>
            </div>
            <div class="col-md-4">

            </div>
        </div>
    </span>
</template>

<script>
    import EvaluarPersona from './evaluarPersona'
    export default {
        components: {'evaluar-persona': EvaluarPersona},
        name: "contenedorEvaluaciones",
        props: {
            'prop-personas': {
                default: ''
            },
            'prop-actividad' : {
                default: ''
            },
            'prop-respuestas': {
                default: ''
            },
            'prop-inscriptos': {
                default: ''
            }
        },
        data: function () {
            return {
                personas: [],
                personaSeleccionada: {},
                actividad: {},
                respuestas: [],
                listadoInscriptos: []
            }
        },
        created: function () {
            this.personas = JSON.parse(this.propPersonas);
            this.respuestas = JSON.parse(this.propRespuestas);
            this.actividad = JSON.parse(this.propActividad);
            this.listadoInscriptos = JSON.parse(this.propInscriptos);
        },
        methods: {
            onSearchNoEvaluado: function (text, loading) {
                loading(true);
                this.searchNoEvaluado(loading, text, this);
            },
            searchNoEvaluado: function(loading, text, vm) {
                if (text.length > 2) {
                    //let url = "/ajax/coordinadores?coordinador=" + encodeURI(text);
                    let url = "/ajax/evaluaciones";
                    let payload = {
                        persona: encodeURI(text)
                    };
                    this.axiosGet(url,
                        function (response, vm) {
                            vm.listadoNoEvaluados = [];
                            for (let i = 0, len = response.data.length; i < len; i++) {
                                vm.listadoNoInscriptos.unshift(
                                    {idPersona: response.data[i].idPersona, nombre: response.data[i].nombre}
                                );
                            }
                            loading(false);
                        },
                        payload,
                        function () {
                            // en caso de error
                        }
                    );
                }
            },
        }
    }
</script>

<style scoped>

</style>