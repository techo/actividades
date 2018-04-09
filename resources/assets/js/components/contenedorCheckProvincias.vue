<template>
    <div class="acordion">
        <a class="btn btn-primary dropdown-button" data-toggle="collapse" href="#provincias" role="button" aria-expanded="false">Provincias <i class="fas fa-caret-down"></i></a>
        <div id="provincias" class="collapse" style="border:black 1px">

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
 .acordion .collapsing, .acordion .collapse.in {
     position: absolute !important;
     z-index: 20;
     width: auto;
     overflow:visible;
     background-color:#eee;
     border:1px solid #ddd;
     border-bottom-left-radius:5px;
     border-bottom-right-radius:5px;
     padding: 5% 5%;
 }

 .dropdown-button {
     width: 100%;
 }
</style>