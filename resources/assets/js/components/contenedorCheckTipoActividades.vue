<template>
    <div>
        <a class="btn btn-primary" data-toggle="collapse" href="#tipoActividades" role="button" aria-expanded="false">Tipos de Actividad</a>
        <div id="tipoActividades" class="collapse">
                <check-tipo-actividades
                    v-bind:proptipos="listaTipos"
                >
                </check-tipo-actividades>

            <a href="#" v-on:click="borrar">Borrar</a>
            <a href="#" v-on:click="aplicar">Aplicar</a>
        </div>
    </div>
</template>

<script>
    import CheckTipoActividades from './checkTipoActividades';

    export default {
        name: "contenedor-check-tipo-actividades",
        props: ['proptipos'],
        components: {'check-tipo-actividades': CheckTipoActividades},
        data () {
            return {
                listaTipos: this.proptipos,
                selected: []
            }
        },
        methods: {
            aplicar(e) {
               e.preventDefault();
               let seleccionados = [];
               for (let i =0; i < this.$children.length; i++) {
                   seleccionados.push(this.$children[i].selected);
               }
               this.$parent.dataTiposActividad = [].concat.apply([], seleccionados);
               // $('#tipoActividades').hide();
            },

            borrar(e) {
                e.preventDefault();
                for (let i =0; i < this.$children.length; i++) {
                    this.$children[i].selected = [];
                }
                this.$parent.dataLocalidades = [];
            },
            seleccionarTodos() {
                for (let i =0; i < this.$children.length; i++) {
                    this.$children[i].selectAll = !this.$children[i].selectAll;
                }
            },
        },
        watch: {
            listaTipos(){
                for (let i=0; i< this.$children.length; i++) {
                    this.$children[i].datos = this.listaTipos;
                }
            }
        }
    }
</script>

<style scoped>

</style>