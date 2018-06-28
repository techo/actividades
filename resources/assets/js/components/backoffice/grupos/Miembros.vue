<template>
    <div>
        <modal-voluntario :voluntario="voluntario"></modal-voluntario>
        <div id="breadcrumb">
            <ol class="breadcrumb">
                <li v-for="(item, key) in breadcrumb" :class="{active: (key === breadcrumb.length-1)}">
                    <a v-if="breadcrumb.indexOf(item) !== breadcrumb.length-1" @click="actualizarTabla(item)">
                        {{ item.nombre }}
                    </a>
                    <span v-else>{{ item.nombre }}</span>
                </li>
            </ol>
        </div>
    </div>
</template>

<script>
    import ModalVoluntario from './ModalVoluntario';

    export default {
        name: "Miembros",
        props: ['actividad', 'items', 'id-grupo-raiz'],
        components: {'modal-voluntario': ModalVoluntario},
        data: function () {
            return {
                dataActividad: {},
                miembros: {
                    arbol: []
                },
                campos: [],
                idGrupoActual: 0,
                breadcrumb: [],
                loading: false,
                paginationData: {},
                urlGrupos: '',
                registrosPorPag: 10,
                idRaiz: JSON.parse(this.idGrupoRaiz),
                voluntario: {}
            }
        },
        created: function() {
            this.dataActividad = JSON.parse(this.actividad);
            this.miembros = JSON.parse(this.items);
            this.breadcrumb.push({nombre: this.dataActividad.nombreActividad, id: this.miembros.idRaiz});
            this.idGrupoActual = this.miembros.idRaiz;
            this.paginationData.firstPageUrl +=  this.miembros.idRaiz + '/miembros?page=1';
            Event.$on('btnGrupoPersona:guardar-grupo', this.guardarGrupo);
            Event.$on('btnGrupoPersona:guardar-inscripto', this.guardarInscripto);
            Event.$on('btnGrupoPersona:guardar-no-inscripto', this.guardarNoInscripto);
            Event.$on('vuetable-addToBreadcrumb', this.addToBreadcrumb);
            Event.$on('vuetable-verVoluntario', this.verVoluntario);
            Event.$on('grupos-toolbar:getRoles', this.getRoles);

        },
        computed: {
        },
        methods: {
            actualizarTabla(item) {
                Event.$emit('vuetable-actualizarTabla', item);
                let result = this.buscarMiembroPorNombre(this.breadcrumb, item);
                let pos = result.pos+1;
                this.breadcrumb.splice(pos, this.breadcrumb.length - pos);
                this.idGrupoActual = item.id;
            },
            addToBreadcrumb(item) {
                this.breadcrumb.push(item);
                this.idGrupoActual = item.id;
            },
            guardarGrupo(payload){
                let grupo = {
                    nombre: payload,
                    idActividad: this.dataActividad.idActividad,
                    idPadre: this.idGrupoActual
                };
                let url = '/admin/ajax/grupos';
                this.axiosPost(url, function(result, self) {
                    Event.$emit('vuetable-actualizarTabla', {id: self.idGrupoActual});
                    Event.$emit('inscripciones-actualizar-tabla');
                    Event.$emit('Miembros:guardado');
                }, grupo);
            },
            guardarInscripto(payload) {
                let inscripto = {
                    idPersona: payload.inscripto.idPersona,
                    rol: payload.rol,
                    idActividad: this.dataActividad.idActividad,
                    idGrupo: this.idGrupoActual,
                };
                let url = '/admin/ajax/grupos/'+ this.idGrupoActual +'/inscriptos';
                this.axiosPost(url, function(result, self) {
                    Event.$emit('vuetable-actualizarTabla', {id: self.idGrupoActual});
                    Event.$emit('inscripciones-actualizar-tabla');
                    Event.$emit('Miembros:guardado');
                }, inscripto);
            },
            guardarNoInscripto(payload) {
                let noInscripto = {
                    idPersona: payload.noInscripto.idPersona,
                    rol: payload.rol,
                    idActividad: this.dataActividad.idActividad,
                    idGrupo: this.idGrupoActual,
                    idPuntoEncuentro: payload.idPuntoEncuentro
                };
                let url = '/admin/ajax/actividades/'+ this.dataActividad.idActividad +'/inscripciones';
                this.axiosPost(url, function(result, self) {
                    Event.$emit('inscripciones-actualizar-tabla');
                    Event.$emit('Miembros:guardado');
                }, noInscripto, function (error, self){
                    if (error.response.status === 428) {
                        Event.$emit('Miembros:voluntario-duplicado', error.response.data);
                    }
                });
            },
            findObjectByKey(array, key, value) {
                for (let i = 0; i < array.length; i++) {
                    if (array[i][key] === value) {
                        return {
                            'obj': array[i],
                            'index': i
                        };
                    }
                }
                return null;
            },
            buscarMiembroPorNombre(array,item) {
                for (let i = 0; i < array.length; i++) {
                    if (item.nombre === array[i].nombre) {
                        return {obj:item, pos: i};
                    }
                }
                return null;
            },
            isEmpty(obj) {
                for(let prop in obj) {
                    if(obj.hasOwnProperty(prop))
                        return false;
                }

                return JSON.stringify(obj) === JSON.stringify({});
            },
            esGrupo(obj) {
                return (obj.tipo === 'grupo');
            },
            verVoluntario(user) {
                let url = '/admin/ajax/personas/' + user.id;
                this.axiosGet(url, function (response, self) {
                    self.voluntario = response.data;
                    Event.$emit('vuetable-showModalVoluntario');
                });
            }
        },
        watch: {
        }
    }
</script>

<style scoped>
    a {
        cursor: pointer;
    }

    ol.breadcrumb {
        margin-top: 2em;
    }
</style>