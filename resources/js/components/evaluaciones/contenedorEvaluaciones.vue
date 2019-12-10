<template>
    <span>
        <h4>{{ $t('frontend.feedback_to_your_peers') }}</h4>
        <div v-for="persona in listadoParaEvaluar" class="mt-2">
            <evaluar-persona :persona="persona" :actividad="actividad"></evaluar-persona>
        </div>
        <h4 v-if="evaluados.length > 0">{{ $t('frontend.peers_already_received_feedback') }}</h4>
        <div v-for="persona in evaluados" class="mt-2">
            <evaluar-persona :persona="persona" :actividad="actividad"></evaluar-persona>
        </div>
        <p class="alert alert-info mt-3" v-if="!evaluacionPasada">
            <i class="fa fa-star" style="margin-right: 0.5em"></i>
            {{ $t('frontend.cannot_find_peer') }}
        </p>

        <div class="row"  v-if="!evaluacionPasada">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="listadoInscriptos">{{ $t('frontend.search_volunteer') }}</label>
                    <v-select
                            :options="personasNoEvaluadas"
                            label="nombre"
                            :placeholder="$t('frontend.search_volunteer')"
                            name="listadoInscriptos"
                            id="listadoInscriptos"
                            v-model="personaSeleccionada"
                            :filterable="true"
                    >
                        <span slot="no-options">{{ $t('frontend.type_to_search') }}</span>
                    </v-select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group"> <br>
                    <button class="btn btn-primary" @click="incluirPersona">{{ $t('frontend.include_peer') }}</button>
                </div>
            </div>
        </div>
    </span>
</template>

<script>
    import EvaluarPersona from './evaluarPersona';
    import vSelect2 from 'vue-select';
    import 'vue-select/dist/vue-select.css';
    export default {
        components: {'evaluar-persona': EvaluarPersona, 'v-select': vSelect2 },
        name: "contenedor-evaluaciones",
        props: [
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
                let voluntario; let eliminados = [];
                for (let i = 0; i < this.personasNoEvaluadas.length; i++) {
                    voluntario = this.personasNoEvaluadas[i];
                    // los que estan en mi grupo sin incluirme a mi mismo
                    if (voluntario.idGrupo === this.miGrupo.idGrupo && voluntario.idPersona !== this.user.idPersona) {
                        this.listadoParaEvaluar.push(voluntario);
                    }

                    // los que estan en el grupo padre
                    if (voluntario.idGrupo === this.miGrupo.idPadre) {
                        this.listadoParaEvaluar.push(voluntario);
                    }

                    // los que estan en los grupos subordinados
                    if (this.gruposSubordinados.indexOf(voluntario.idGrupo) !== -1) {
                        this.listadoParaEvaluar.push(voluntario);
                    }
                }

                for (let i = 0; i < this.listadoParaEvaluar.length; i++) {
                    this.excluirUsuario(this.personasNoEvaluadas, this.listadoParaEvaluar[i]);
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