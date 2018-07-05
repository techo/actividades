<template>
    <span>
        <h4>Evalúa a tus compañeros</h4>
        <div v-for="persona in listadoParaEvaluar" class="mt-2">
            <evaluar-persona :persona="persona" :actividad="actividad"></evaluar-persona>
        </div>
        <h4 v-if="evaluados.length > 0">Compañeros ya evaluados</h4>
        <div v-for="persona in evaluados" class="mt-2">
            <evaluar-persona :persona="persona" :actividad="actividad"></evaluar-persona>
        </div>
        <p class="alert alert-info mt-3" v-if="!evaluacionPasada">
            <i class="fa fa-star" style="margin-right: 0.5em"></i>
            ¿No ves a la persona que quieres evaluar? Usa este buscador para incluirla
        </p>

        <div class="row"  v-if="!evaluacionPasada">
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
        props: [
            //'prop-personas',
            'prop-actividad',
            'prop-evaluados',
            'prop-inscriptos',
            'prop-user',
            'prop-mi-grupo',
            'prop-grupos-subordinados'
        ],
        data: function () {
            return {
                listadoParaEvaluar: [],
                personaSeleccionada: null,
                actividad: {},
                respuestas: [],
                listadoInscriptos: [],
                user: {},
                miGrupo: {},
                gruposSubordinados: [],
                personasNoEvaluadas: [],
                evaluados: []
            }
        },
        created: function () {
            let result = {};
            //this.personasAEvaluar = (this.propPersonas === undefined) ? [] : JSON.parse(this.propPersonas);
            this.evaluados = JSON.parse(this.propEvaluados);
            this.actividad = JSON.parse(this.propActividad);
            this.user = JSON.parse(this.propUser);
            this.listadoInscriptos = JSON.parse(this.propInscriptos);
            this.miGrupo = JSON.parse(this.propMiGrupo);
            this.gruposSubordinados = JSON.parse(this.propGruposSubordinados);
            this.personasNoEvaluadas = this.listadoInscriptos.slice(0);
            this.excluirUsuario(this.personasNoEvaluadas, this.user);
            //
            for (let i = 0; i < this.evaluados.length; i++) {
                this.excluirUsuario(this.personasNoEvaluadas, this.evaluados[i])
            }
            this.filtrarInscriptos();
        },
        methods: {
            incluirPersona: function () {
                this.listadoParaEvaluar.push(this.personaSeleccionada);
                let result = this.buscarPersonaPorId(this.personasNoEvaluadas, this.personaSeleccionada);
                this.personasNoEvaluadas.splice(result.pos, 1);
                this.personaSeleccionada = null;
            },
            excluirUsuario: function (array, user) {
                let result = this.buscarPersonaPorId(array, user);
                if (result) {
                    array.splice(result.pos, 1);
                }
            },
            filtrarInscriptos: function () {
                //let inscripcion = this.buscarPersonaPorId(this.listadoInscriptos, this.user);
                //this.user.idGrupo = inscripcion.obj.idGrupo;
                let voluntario;
                for (let i = 0; i < this.personasNoEvaluadas.length; i++) { debugger;
                    voluntario = this.personasNoEvaluadas[i];
                    // los que estan en mi grupo sin incluirme a mi mismo
                    if (voluntario.idGrupo === this.miGrupo.idGrupo && voluntario.idPersona !== this.user.idPersona) {
                        this.incluirEnListadoParaEvaluar(this.personasNoEvaluadas[i]);
                    }

                    // los que estan en el grupo padre
                    if (voluntario.idGrupo === this.miGrupo.idPadre) {
                        this.incluirEnListadoParaEvaluar(this.personasNoEvaluadas[i]);
                    }

                    // los que estan en los grupos subordinados
                    if (this.gruposSubordinados.indexOf(voluntario.idGrupo) !== -1) {
                        this.incluirEnListadoParaEvaluar(voluntario);
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
                this.listadoParaEvaluar.push(persona);
                this.excluirUsuario(this.personasNoEvaluadas, persona);
                //this.listadoInscriptos.splice(result.pos, 1);
            },
        },
        computed: {
            evaluacionPasada: function () {
                let ahora = new Date();
                let fechaFinEvaluaciones = new Date(this.actividad.fechaFinEvaluaciones);
                return ahora.getTime() > fechaFinEvaluaciones.getTime();
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