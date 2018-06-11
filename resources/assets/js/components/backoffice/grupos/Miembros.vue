<template>
    <div>
        <modal-voluntario :voluntario="voluntario"></modal-voluntario>
        <div id="breadcrumb">
            <!-- ToDo: Hacer el Breadcrumb. Otro Componente? -->
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
    import axios from 'axios';

    export default {
        name: "Miembros",
        props: ['actividad', 'items', 'id-grupo-raiz'],
        components: {'modal-voluntario': ModalVoluntario},
        data: function () {
            return {
                dataActividad: {},
                miembros: {},
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
            Event.$on('guardar-grupo', this.guardarGrupo);
            Event.$on('guardar-inscripto', this.guardarInscripto);
            Event.$on('vuetable-addToBreadcrumb', this.addToBreadcrumb);
            Event.$on('vuetable-verVoluntario', this.verVoluntario);
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
            spliceFromBreadcrumb(item) {

            },
            guardarGrupo(payload){
                let grupo = {
                    nombre: payload,
                    idActividad: this.dataActividad.idActividad,
                    idPadre: this.idGrupoActual
                };
                let url = '/admin/ajax/grupos';
                this.axiosPost(url, function(result, self) {
                    self.miembros.arbol.unshift(result.data);
                    Event.$emit('guardado');
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
                    let nuevaPersona = {
                        id: inscripto.idPersona,
                        cantidad: '-',
                        dni: payload.inscripto.dni,
                        nombre: payload.inscripto.nombre,
                        rol: payload.rol,
                        tipo: 'persona'
                    };
                    //self.miembros.arbol.unshift(nuevaPersona);
                    Event.$emit('vuetable-actualizarTabla', {id: self.idGrupoActual});
                    Event.$emit('guardado');
                }, inscripto);
            },
            axiosPost(url, fCallback, params = []) {
                this.loading = true;
                axios.post(url, params)
                    .then(response => {
                        fCallback(response.data, this);
                        this.loading = false;

                    })
                    .catch((error) => {
                        this.loading = false;
                        // Error
                        console.error('Error en el post: ' + url);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.error(error.response.data);
                            console.error(error.response.status);
                            console.error(error.response.headers);
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.error(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
                    });
            },
            axiosGet(url, fCallback, params = []) {
                this.loading = true;
                axios.get(url, params)
                    .then(response => {
                        fCallback(response.data, this);
                        this.loading = false;

                    })
                    .catch((error) => {
                        this.loading = false;
                        // Error
                        console.error('Error en el post: ' + url);
                        if (error.response) {
                            // The request was made and the server responded with a status code
                            // that falls out of the range of 2xx
                            console.error(error.response.data);
                            console.error(error.response.status);
                            console.error(error.response.headers);
                        } else if (error.request) {
                            // The request was made but no response was received
                            // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                            // http.ClientRequest in node.js
                            console.error(error.request);
                        } else {
                            // Something happened in setting up the request that triggered an Error
                            console.error('Error', error.message);
                        }
                        console.error(error.config);
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
            actualizarPaginationData(response) {
                this.paginationData.firstPageUrl = response.first_page_url;
                this.paginationData.lastPageUrl = response.last_page_url;
                this.paginationData.nextPageUrl = response.next_page_url;
                this.paginationData.prevPageUrl = response.prev_page_url;
                this.paginationData.from = response.from;
                this.paginationData.to = response.to;
                this.paginationData.total = response.total;
                this.perPage = response.per_page;
                this.paginationData.currentPage = response.current_page;
                this.paginationData.lastPageUrl = response.last_page_url;
                this.paginationData.lastPage = response.last_page;
                this.paginationData.pageCount = Math.ceil((response.total/response.per_page));

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
            // 'paginationData.currentPage': function (nuevo, viejo) {
            //     this.$refs.paginacion.selected = nuevo;
            // }
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