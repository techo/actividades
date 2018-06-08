<template>
    <div>
        <div id="breadcrumb">
            <!-- ToDo: Hacer el Breadcrumb. Otro Componente? -->
            <ol class="breadcrumb">
                <li v-for="(item, key) in breadcrumb" :class="{active: (key === breadcrumb.length-1)}">
                    <a v-if="breadcrumb.indexOf(item) !== breadcrumb.length-1" @click="actualizarBreadcrumb(item)">
                        {{ item.nombre }}
                    </a>
                    <span v-else>{{ item.nombre }}</span>
                </li>
            </ol>
        </div>
        <div v-if="this.miembros.arbol.length > 0">
            <span v-if="!loading">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox">
                                </th>
                                <th>
                                    Tipo
                                </th>
                                <th>
                                    Nombre
                                </th>
                                <th>
                                    Rol
                                </th>
                                <th>
                                    Miembros
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="miembro in miembros.arbol">
                                <td>
                                    <input type="checkbox">
                                </td>
                                <td>
                                    <span>
                                        <i class="fa" :class="{'fa-users': esGrupo(miembro), 'fa-user': !esGrupo(miembro)}"></i>
                                    </span>

                                </td>
                                <td>
                                    <p>
                                        <a @click="actualizarTabla(miembro)">
                                        {{ miembro.nombre }}
                                        </a>
                                    </p>
                                </td>
                                <td>
                                    <p>{{ miembro.rol }}</p>
                                </td>
                                <td>
                                    <p>{{ miembro.cantidad }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <p class="text-muted" style="padding-top: 2em">
                            <i> Registros desde el {{ paginationData.from }} al {{ paginationData.to}}</i>
                        </p>
                    </div>
                    <div class="col-md-10">
                        <paginate
                                :page-count="paginationData.pageCount"
                                :container-class="'pagination'"
                                :prev-text="'Anterior'"
                                :next-text="'Siguiente'"
                                :click-handler="clickPagination"
                                :force-page="paginationData.currentPage-1"
                                ref="paginacion"
                        >
                        </paginate>
                    </div>
                </div>

            </span>
            <div v-else  class="row">
                <div class="col-md-1 col-md-offset-5">
                    <i class="fa fa-refresh fa-spin fa-5x fa-fw"></i>
                </div>
            </div>

        </div>
        <div v-else>
            <p><i>No hay grupos ni personas asignadas</i></p>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import Paginate from 'vuejs-paginate'; // https://github.com/lokyoung/vuejs-paginate
    export default {
        name: "Miembros",
        props: ['actividad', 'items'],
        components: {'paginate': Paginate},
        data: function () {
            return {
                dataActividad: {},
                miembros: {},
                padreActual: 0,
                breadcrumb: [],
                loading: false,
                paginationData: {},
                urlGrupos: '',
                registrosPorPag: 2,
                pagSeleccionada: 1
            }
        },
        created: function() {
            this.dataActividad = JSON.parse(this.actividad);
            this.miembros = JSON.parse(this.items);
            this.breadcrumb.push({nombre: this.dataActividad.nombreActividad, id: this.miembros.idRaiz});
            this.actualizarBreadcrumb({id: this.miembros.idRaiz, nombre: this.dataActividad.nombreActividad});
            this.padreActual = this.miembros.idRaiz;
            this.paginationData.firstPageUrl +=  this.miembros.idRaiz + '/miembros?page=1';
            Event.$on('guardar-grupo', this.guardarGrupo);
            Event.$on('guardar-inscripto', this.guardarInscripto);

        },
        computed: {
        },
        methods: {
            actualizarTabla(miembro) {
                let url = '/admin/ajax/grupos/'+ miembro.id +'/miembros';
                this.padreActual = miembro.id;
                this.axiosPost(url, function(response, self){
                    self.miembros.arbol = response.data;
                    self.breadcrumb.push(miembro);
                    self.actualizarPaginationData(response);
                    self.urlGrupos = response.path;
                });
            },
            actualizarBreadcrumb(item) {
                let result = this.buscarMiembroPorNombre(this.breadcrumb, item);
                let miembro = result.obj;
                let pos = result.pos;
                this.breadcrumb.splice(pos, this.breadcrumb.length - pos);
                if (miembro.id === 0) {
                    miembro.id = this.miembros.idRaiz;
                }
                this.actualizarTabla(miembro);
            },
            guardarGrupo(payload){
                let grupo = {
                    nombre: payload,
                    idActividad: this.dataActividad.idActividad,
                    idPadre: this.padreActual
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
                    idGrupo: this.padreActual,
                };
                let url = '/admin/ajax/grupos/'+ this.padreActual +'/inscriptos';
                this.axiosPost(url, function(result, self) {
                    let nuevaPersona = {
                        id: inscripto.idPersona,
                        cantidad: '-',
                        dni: payload.inscripto.dni,
                        nombre: payload.inscripto.nombre,
                        rol: payload.rol,
                        tipo: 'persona'
                    };
                    self.miembros.arbol.unshift(nuevaPersona);
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
                        console.error('Error en el post: ' + url); debugger;
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
            clickPagination(pageNum) {
                let url = this.urlGrupos;
                let payload = {
                    per_page: this.registrosPorPag,
                    page: pageNum
                };
                this.axiosPost(url, function(response, self) {
                    if (typeof response.data === 'object') {
                        self.miembros.arbol = Object.values(response.data);
                    } else {
                        self.miembros.arbol = response.data;
                    }
                    self.actualizarPaginationData(response);
                }, payload);
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

            }
        },
        watch: {
            'paginationData.currentPage': function (nuevo, viejo) {
                this.$refs.paginacion.selected = nuevo; console.log(nuevo + ', '+viejo);
            }
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