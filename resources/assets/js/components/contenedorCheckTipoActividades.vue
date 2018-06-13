<template>
    <div class="acordion">
        <a 
            v-bind:class="{active: $parent.dataTiposActividad.length > 0}"
            data-toggle="collapse" href="#tipoActividades" 
            role="button" aria-expanded="false"
            ><span> Tipos de Actividad </span> <i class="fas fa-caret-down"></i>
        </a>
        <div id="tipoActividades" class="collapse lista-opciones" >
                
            <div v-for="tipo in listaTipos">
                <check-tipo-actividades class="opciones"
                    v-bind:key="tipo.id"
                    v-bind:propdatos="tipo"
                >
                </check-tipo-actividades>

            </div>
            <button class="btn btn-sm" type="button" v-on:click="borrar">Borrar</button>
            <button class="btn btn-sm aplicar" type="button" v-on:click="aplicar">Aplicar</button>
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
                selected: [],
                selectAll: false
            }
        },
        methods: {
            aplicar(e) {
               let seleccionados = [];
               for (let i =0; i < this.$children.length; i++) {
                   seleccionados.push(this.$children[i].selected);
               }
               this.$parent.dataTiposActividad = [].concat.apply([], seleccionados);
               $('#tipoActividades').collapse('hide')
            },

            borrar() {
                for (let i =0; i < this.$children.length; i++) {
                    this.$children[i].selected = [];
                }
                this.$parent.dataTiposActividad = [];
                $('#tipoActividades').collapse('hide')
            },
            // seleccionarTodos() {
            //     for (let i =0; i < this.$children.length; i++) {
            //         this.$children[i].selectAll = !this.$children[i].selectAll;
            //     }
            // }

        },
        computed: {
            listaTipos: {
                get: function() {
                    return this.propdatos;
                },
                set: function(nuevoValor) {}
            }
        }
    }
</script>

<style scoped>
    .acordion {
        height: 100%;
        margin-left: -1px;
        margin-right: -1px;
    }
    .acordion a {
        color: #494848;
        display: inline-block;
        height: 100%;
        padding-top: 11px;
        text-decoration: none;
        vertical-align: middle;
        width: 100%;
    }
    .acordion a span {
        margin: auto 15px;
    }
    .opciones {
        color: black;
        font-weight: 500;
    }
    .opciones strong {
        font-weight: 900;
    }
    .acordion a.active {
        color: #0092dd;
    }
    .acordion a svg {
        color: #494848;
        float: right;
        margin-right: 15px;
    }
    .aplicar {
        background-color: #0092dd;
        color: #fff;
    }
    .acordion .collapsing, .acordion .collapse.show {
        z-index: 20;
        width: 100%;
        overflow:visible;
        border-bottom-left-radius:5px;
        border-bottom-right-radius:5px;
        padding: 5% 5%;
    }
    .lista-opciones {
        background: #fff;
        border: 1px solid #0092dd;
        border-top: 1px solid #a9a9a9;
    }
    .acordion .collapsing.lista-opciones {
        display: none;
    }
    .dropdown-button {
        width: 100%;
    }
</style>