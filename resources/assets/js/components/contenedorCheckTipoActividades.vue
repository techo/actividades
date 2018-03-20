<template>
    <div>
        <a class="btn btn-primary" data-toggle="collapse" href="#tipoActividades" role="button" aria-expanded="false">Tipos de Actividad</a>
        <div id="tipoActividades" class="collapse">
                <check-tipo-actividades
                    v-bind:propdatos="listaTipos"
                >
                </check-tipo-actividades>

            <button class="btn btn-sm" type="button" v-on:click="borrar">Borrar</button>
            <button class="btn btn-sm" type="button" v-on:click="aplicar">Aplicar</button>
        </div>
    </div>
</template>

<script>
    import CheckTipoActividades from './checkTipoActividades';

    export default {
        name: "contenedor-check-tipo-actividades",
        props: ['propdatos'],
        components: {'check-tipo-actividades': CheckTipoActividades},
        data () {
            return {
                listaTipos: this.propdatos,
                selected: []
            }
        },
        methods: {
            aplicar() {

               let seleccionados = [];
               for (let i =0; i < this.$children.length; i++) {
                   seleccionados.push(this.$children[i].selected);
               }
               this.$parent.dataTiposActividad = [].concat.apply([], seleccionados);
               // $('#tipoActividades').hide();
            },

            borrar() {

                for (let i =0; i < this.$children.length; i++) {
                    this.$children[i].selected = [];
                }
                this.$parent.dataTiposActividad = [];
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