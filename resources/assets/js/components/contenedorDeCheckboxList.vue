<template>
    <div>
        <a href="#" class="btn btn-primary" data-toggle="collapse">Provincias</a>
        <div id="provincias" class="collapse">
            <checkbox-list
                v-for="provincia in listaProvincias"
                :key="provincia.id_provincia"
                v-bind:propdatos="provincia"
            >
            </checkbox-list>
            <a href="#" v-on:click="borrar">Borrar</a>
            <a href="#" v-on:click="aplicar">Aplicar</a>
        </div>
    </div>
</template>

<script>
    import CheckboxList from './checkboxList';

    export default {
        name: "contenedor-de-checkbox-list",
        props: ['provincias'],
        components: {'checkbox-list': CheckboxList},
        data () {
            return {
                selected: [],
                listaProvincias: this.provincias
            }
        },
        methods: {
            aplicar(e) {
               e.preventDefault();
               let seleccionados = [];
               for (let i =0; i < this.$children.length; i++) {
                   seleccionados.push(this.$children[i].selected);
               }
               this.selected = [].concat.apply([], seleccionados);
            },

            borrar(e) {
                e.preventDefault();
                for (let i =0; i < this.$children.length; i++) {
                    this.$children[i].selected = [];
                }
            }
        }
    }
</script>

<style scoped>

</style>