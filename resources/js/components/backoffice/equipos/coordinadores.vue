<template>
    <div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">{{ $t('backend.activity_coordination') }}</h3>
                <p class="help-block">
                    <ul>
                        <li>{{ $t('backend.all_these_people_can_edit_the_team') }}</li>
                    </ul>
                </p>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group">
                            <v-select 
                                :options="personas" 
                                @search="onSearch" 
                                label="{{ $t('backend.name') }}"
                                v-model="persona" 
                                :filterable="false"
                                :selectOnTab="true"
                                @input="enviado = false;"
                                class="select_persona"
                            >
                                <template slot="no-options">{{ $t('backend.enter_name_surname_or_dni') }}</template>
                            </v-select>
                            <span class="input-group-btn">
                                <button :class="{ 'btn': true, 'btn-primary': !enviado, 'btn-success': enviado }" :disabled="deshabilitado || enviado" @click="guardar()" v-text="(enviado)? $t('backend.sent') : $t('backend.save')"></button>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <ul>
                        <li v-for="coordinador in coordinadores" :key='coordinador.idPersona'>
                            {{ coordinador.nombre }} 
                            <span class="input-group-btn">          
                                <button :class="{ 'btn': true, 'btn-danger': true }" @click="eliminar(coordinador.idCoordinador)" v-text="$t('backend.eliminate')" v-if="idPersonaCreacion !=  coordinador.idPersona"></button>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import vSelect from 'vue-select';

    export default {
        name: "accesos",
        props: ['id','idPersonaCreacion'],
        components: { vSelect },
        data() {
            return {
                persona: null,
                personas: [],
                coordinadores: [],
                enviado: false,
                mensaje: null,
            }
        },
        created() {},
        mounted() {
            this.getCoordinadores();
        },
        computed: {
            nombre() {
                if(this.persona)
                    return this.persona.nombres + ' ' + this.persona.apellidoPaterno + ' (' + this.persona.mail + ')'
            },
            deshabilitado() {
                if(this.persona)
                    if(this.persona.hasOwnProperty('idPersona'))
                        return false;
                return true;
            }
        },
        filters: {},
        watch: {},
        methods: {
            guardar(){
                axios.post('/admin/ajax/equipos/' + this.idEquipo + '/coordinadores/' + this.persona.idPersona)
                    .then((datos) => { 
                        this.persona = null;
                        this.enviado = true;
                        this.coordinadores = this.getPersona();
                    })
                    .catch((error) => { debugger; });
            },
            getPersona(){
                axios.get('/admin/ajax/equipos/' + this.id + '/coordinadores')
                    .then((datos) => { this.coordinadores = datos.data; })
                    .catch((error) => { debugger; });
            },
            eliminar(idCoordinador){
                axios.post('/admin/ajax/actividades/' + this.id + '/accesos/' + idCoordinador + '/borrar')
                    .then((datos) => { this.mensaje = datos.data; this.getPersona(); })
                    .catch((error) => { debugger; });
            },
            onSearch: _.debounce( function (text, loading) {
                if(text.length > 3) {
                    loading(true);
                    axios.get('/ajax/coordinadores?coordinador=' + text)
                        .then((datos) => { 
                            this.personas = datos.data.data; 
                            loading(false);
                        })
                        .catch((error) => { 
                            console.log(error);
                            loading(false);
                        });
                }
            }, 400),
        }
    }
</script>

<style>
.select_persona .vs__dropdown-toggle {
    padding: 3px 0 4px 0;
    border-radius: 3px 0 0 3px;
}
</style>