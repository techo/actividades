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
            <a href="#" v-on:click="borrar">Borrar</a>
            <a href="#" v-on:click="aplicar">Aplicar</a>
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
                listaProvincias: this.provincias,
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
               this.$parent.dataLocalidades = [].concat.apply([], seleccionados);
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
            }

        },
        // computed: {
        //     selectAll: {
        //         get: function () {
        //             let i=0;
        //             this.$children.forEach(function (checkboxList) {
        //                 checkboxList.selectAll ? i++ : false;
        //             });
        //             console.warn('get');
        //             return this.listaProvincias ? this.listaProvincias.length === i : false;
        //         },
        //         set: function (value) {
        //             var selected = [];
        //
        //             if (value) {
        //                 this.$children.forEach(function (checkboxList) {
        //                     checkboxList.selectAll = !checkboxList.selectAll;
        //                 });
        //             }
        //
        //             this.selected = selected;
        //         }
        //     }
        // }
    }
</script>

<style scoped>

</style>