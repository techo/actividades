<template>
    <div>
        <label for="toolbar">Acciones </label>
        <div class="btn-group" role="group" aria-label="toolbar" id="toolbar">
            <button
                    type="button"
                    class="btn btn-sm btn-default"
                    :class="{'disabled': disabled}"
                    @click="mostrarRolModal">
                Asignar Rol
            </button>
            <button
                    type="button"
                    class="btn btn-sm btn-default"
                    :class="{'disabled': disabled}"
                    @click="mostrarGrupoModal">
                Asignar Grupo
            </button>
            <button
                    type="button"
                    class="btn btn-sm btn-default"
                    :class="{'disabled': disabled}"
                    @click="mostrarEliminarModal">
                Remover de grupo
            </button>
            <rol-modal></rol-modal>
            <grupo-modal></grupo-modal>
            <borrar-modal></borrar-modal>
        </div>

    </div>
</template>

<script>
    import GrupoModal from './GrupoModal';
    import RolModal from './RolModal';
    import BorrarModal from './BorrarGrupoModal';

    export default {
        name: "GruposToolbar",
        components: {'grupo-modal': GrupoModal, 'rol-modal': RolModal, 'borrar-modal': BorrarModal},
        data() {
           return {
               selected: []
            }
        },
        created(){
            Event.$on('vuetable-actualizarTabla', this.clearSelected);
            Event.$on('MiembrosTabla:miembro-seleccionado', this.actualizarSeleccionados);
            Event.$on('MiembrosTabla:miembro-seleccionado-todos', this.actualizarSeleccionadosTodos);
        },
        methods: {
            mostrarRolModal: function () {
                if(this.disabled) return;
                Event.$emit('GruposToolbar:show-rol-modal');
            },
            mostrarGrupoModal: function () {
                if(this.disabled) return;
                Event.$emit('GruposToolbar:show-grupo-modal');
            },
            mostrarEliminarModal: function () {
                if(this.disabled) return;
                Event.$emit('GruposToolbar:show-borrar-modal');
            },
            actualizarSeleccionados: function (data) {
                if (data.status) {
                    this.selected.push(data.obj);
                    return this.selected;
                }
                let result = this.buscarMiembroPorNombre(this.selected, data.obj);
                if (result) {
                    this.selected.splice(result.pos, 1);
                }
            },
            actualizarSeleccionadosTodos: function (data) {
                this.selected = [];
                if (data.selected) {
                    for (let i = 0; i < data.tableData.length; i++) {
                        this.selected.unshift(data.tableData[i]);
                    }
                }
            },
            buscarMiembroPorNombre(array,item) {
                for (let i = 0; i < array.length; i++) {
                    if (item.nombre === array[i].nombre) {
                        return {obj:item, pos: i};
                    }
                }
                return null;
            },
            clearSelected: function() {
                this.selected = [];
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
        },
        computed: {
            disabled: function () {
                return (this.selected.length === 0);
            }
        }
    }
</script>

<style scoped>

</style>