<template>
    <span>
        <h4>Evalúa a tus compañeros</h4>
        <div v-for="persona in personas" class="mt-2">
            <evaluar-persona :persona="persona" :actividad="actividad"></evaluar-persona>
        </div>
        <p class="alert alert-info mt-3">
                    <i class="fa fa-star" style="margin-right: 0.5em"></i>
                    ¿No ves a la persona que quieres evaluar? Usa este buscador para incluirla
        </p>

        <div class="row">
            <div class="col-md-9">
                <div class="form-group">
                    <label for="listadoInscriptos">Nombre, apellido o DNI del voluntario</label>
                    <v-select
                            :options="personasNoEvaluadas"
                            label="nombre"
                            placeholder="Escribe el nombre, apellido o DNI"
                            name="listadoInscriptos"
                            id="listadoInscriptos"
                            v-model="personaSeleccionada"
                            :filterable=true
                    >
                    </v-select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group"> <br>
                    <button class="btn btn-primary" @click="incluirPersona">Incluir en mis evaluaciones</button>
                </div>
            </div>
        </div>
    </span>
</template>

<script>
    import EvaluarPersona from './evaluarPersona';
    import vSelect2 from 'vue-select';
    export default {
        components: {'evaluar-persona': EvaluarPersona, 'v-select': vSelect2 },
        name: "contenedor-evaluaciones",
        props: [ 'prop-personas', 'prop-actividad', 'prop-respuestas', 'prop-inscriptos', 'prop-user', 'prop-mi-grupo', 'prop-grupos-subordinados'],
        data: function () {
            return {
                personas: [],
                personaSeleccionada: null,
                actividad: {},
                respuestas: [],
                listadoInscriptos: [],
                user: {},
                miGrupo: {},
                gruposSubordinados: [],
                personasNoEvaluadas: []
            }
        },
        created: function () {
            let result = {};
            this.personas = (this.propPersonas === undefined) ? [] : JSON.parse(this.propPersonas);
            //this.respuestas = JSON.parse(this.propRespuestas);
            this.actividad = JSON.parse(this.propActividad);
            this.user = JSON.parse(this.propUser);
            this.listadoInscriptos = JSON.parse(this.propInscriptos);
            result = this.buscarPersonaPorId(this.listadoInscriptos, this.user);
            this.listadoInscriptos.splice(result.pos, 1);
            this.miGrupo = JSON.parse(this.propMiGrupo);
            this.gruposSubordinados = JSON.parse(this.propGruposSubordinados);
            this.filtrarInscriptos();
            this.personasNoEvaluadas = this.listadoInscriptos.filter(this.filtrarPorIdPersona(this.personas));
        },
        methods: {
            incluirPersona: function () {
                this.personas.push(this.personaSeleccionada);
                let result = this.buscarPersonaPorId(this.personasNoEvaluadas, this.personaSeleccionada); console.info(result);
                this.personasNoEvaluadas.splice(result.pos, 1);
                this.personaSeleccionada = null;
            },
            filtrarInscriptos: function () {
                //let inscripcion = this.buscarPersonaPorId(this.listadoInscriptos, this.user);
                //this.user.idGrupo = inscripcion.obj.idGrupo;
                let voluntario; console.info(JSON.stringify(this.listadoInscriptos));
                for (let i = 0; i < this.listadoInscriptos.length; i++) {
                    voluntario = this.listadoInscriptos[i];
                    // los que estan en mi grupo sin incluirme a mi mismo
                    if (this.listadoInscriptos[i].idGrupo === this.miGrupo.idGrupo && this.listadoInscriptos[i].idPersona !== this.user.idPersona) {
                        //this.personas.push(this.listadoInscriptos[i]);
                        this.incluirEnListadoParaEvaluar(this.listadoInscriptos[i]);
                    }

                    // los que estan en el grupo padre
                    if (this.listadoInscriptos[i].idGrupo === this.miGrupo.idPadre) {
                        //this.personas.push(this.listadoInscriptos[i]);
                        this.incluirEnListadoParaEvaluar(this.listadoInscriptos[i]);
                    }

                    // los que estan en los grupos subordinados
                    if (this.gruposSubordinados.indexOf(this.listadoInscriptos[i].idGrupo) !== -1) {
                        //this.personas.push(this.listadoInscriptos[i]);
                        this.incluirEnListadoParaEvaluar(this.listadoInscriptos[i]);
                    }
                }
            },
            buscarPersonaPorId(array, persona) {
                for (let i = 0; i < array.length; i++) {
                    if (persona.idPersona === array[i].idPersona) {
                        return {obj:array[i], pos: i};
                    }
                }
                return null;
            },
            incluirEnListadoParaEvaluar(persona) {
                this.personas.push(persona);
                let result = this.buscarPersonaPorId(this.listadoInscriptos, persona);
                //this.listadoInscriptos.splice(result.pos, 1);
            },

            filtrarPorIdPersona(otherArray){
                return function(current){
                    return otherArray.filter(function(other){
                        return other.idPersona == current.idPersona
                    }).length == 0;
                }
            }

        }
    }
</script>

<style scoped>
    evaluar-persona {
        margin: 1em 1em;
    }

    h4 {
        margin-top: 1em;
        margin-bottom: 1em;
    }
</style>