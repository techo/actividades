<template>
    <div>
        <a class="btn btn-primary" data-toggle="collapse" href="#provincias" role="button" aria-expanded="false">Provincias</a>
        <div id="provincias" class="collapse">

            <div v-for="provincia in listaProvincias">
                <check-provincias
                    :key="provincia.id_provincia"
                    v-bind:propdatos="provincia"
                >
                </check-provincias>

            </div>
            <button class="btn btn-sm" type="button" v-on:click="borrar">Borrar</button>
            <button class="btn btn-sm" type="button" v-on:click="aplicar">Aplicar</button>
        </div>
    </div>
</template>

<script>
    import CheckProvincias from './checkProvincias';

    export default {
        name: "contenedor-check-provincias",
        props: ['provincias'],
        components: {'check-provincias': CheckProvincias},
        data () {
            return {
                selected: [],
                selectAll: false
            }
        },
        methods: {
            aplicar() {
               let seleccionados = [];
               for (let i =0; i < this.$children.length; i++) {
                   seleccionados.push(this.$children[i].selected);
               }
               this.$parent.dataLocalidades = [].concat.apply([], seleccionados);
            },

            borrar() {
                for (let i =0; i < this.$children.length; i++) {
                    this.$children[i].selected = [];
                }
                this.$parent.dataLocalidades = [];
            },
            // seleccionarTodos() {
            //     for (let i =0; i < this.$children.length; i++) {
            //         this.$children[i].selectAll = !this.$children[i].selectAll;
            //     }
            // }

        },
        computed: {
            listaProvincias: {
                get: function() {
                    return this.provincias;
                },
                set: function(nuevoValor) {}
            }
        }
    }
</script>

<style scoped>

</style>