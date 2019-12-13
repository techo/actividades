<template>
    <div>
        <div class="box">
            <div class="box-header with-border"> </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="coordinador">Coordinador</label>
                            <v-select 
                                :options="personas" 
                                @search="onSearch" 
                                label="nombre" 
                                v-model="persona" 
                                :filterable="false"
                                :selectOnTab="true"
                                @search:focus=""
                            >
                                <template slot="no-options">Escribe el nombre, apellido o DNI</template>
                            </v-select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button class="btn btn-primary pull-right" @click="guardar()">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import vSelect from 'vue-select';

    export default {
        name: "accesos",
        props: ['id',],
        components: { vSelect },
        data() {
            return {
                persona: null,
                personas: [],
            }
        },
        created() {},
        mounted() {
            this.getPersona();
        },
        computed: {
            nombre() {
                if(this.persona)
                    return this.persona.nombres + ' ' + this.persona.apellidoPaterno + ' (' + this.persona.mail + ')'
            }
        },
        filters: {},
        watch: {},
        methods: {
            guardar(){
                axios.post('/admin/ajax/actividades/' + this.id + '/accesos/' + this.persona.idPersona)
                    .then((datos) => { this.persona = datos.data; })
                    .catch((error) => { debugger; });
            },
            getPersona(){
                axios.get('/admin/ajax/actividades/' + this.id + '/accesos')
                    .then((datos) => { this.persona = datos.data; })
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

<style scoped>

</style>