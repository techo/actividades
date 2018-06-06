<template>
    <span>
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
        <div v-if="!this.isEmpty(this.miembros)">
            <table class="table table-hover" v-if="!loading">
                <thead>
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
            <div v-else  class="row">
                <div class="col-md-1 col-md-offset-5">
                    <i class="fa fa-refresh fa-spin fa-5x fa-fw"></i>
                </div>
            </div>

        </div>
        <div v-else>
            <p><i>No hay grupos ni personas asignadas</i></p>
        </div>
    </span>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "Miembros",
        props: ['actividad', 'items'],
        data: function () {
            return {
                dataActividad: {},
                miembros: {},
                padreActual: 0,
                breadcrumb: [],
                loading: false,
            }
        },
        created: function() {
            this.dataActividad = JSON.parse(this.actividad);
            this.miembros = JSON.parse(this.items);
            this.breadcrumb.push({nombre: this.dataActividad.nombreActividad, id: 0});
            Event.$on('guardar-grupo', this.guardarGrupo);
            Event.$on('guardar-inscripto', this.guardarInscripto);

        },
        computed: {
        },
        methods: {
            actualizarTabla(miembro) {
                let url = '/admin/ajax/grupos/'+ miembro.id +'/miembros';
                this.axiosPost(url, function(response, self){
                    self.miembros.arbol = response.data;
                    self.breadcrumb.push(miembro);
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
                this.axiosPost(url, function(data, self) {
                    self.miembros.push(data);
                    Even.$emit('guardado');
                }, grupo);
            },
            guardarInscripto(payload) {
                let inscripto = {
                    idPersona: payload.inscripto.idPersona,
                    rol: payload.rol,
                    idActividad: this.dataActividad.idActividad,

                };
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
                        console.error('Error en: ' + url);
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
                for(var prop in obj) {
                    if(obj.hasOwnProperty(prop))
                        return false;
                }

                return JSON.stringify(obj) === JSON.stringify({});
            },
            esGrupo(obj) {
                return (obj.tipo === 'grupo');
            },
        },
        filters: {
        }
    }
</script>

<style scoped>
    a {
        cursor: pointer;
    }
</style>