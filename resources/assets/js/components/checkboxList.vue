<template>
    <div>
        <input type="checkbox" v-model="selectAll">
        <strong>{{ this.datos.provincia }}</strong>

        <div v-for="l in this.datos.localidades">

            <input type="checkbox" :value="l.id_localidad" v-model="selected">
            {{ l.localidad }}
        </div>
        <hr>
    </div>
</template>

<script>
    export default {
        name: "checkbox-list",
        props: ['propdatos'],
        data() {
            return {
                selected: [],
                datos: this.propdatos
            }
        },
        computed: {
            selectAll: {
                get: function () {
                    return this.datos.localidades ? this.selected.length == this.datos.localidades.length : false;
                },
                set: function (value) {
                    var selected = [];

                    if (value) {
                        this.datos.localidades.forEach(function (localidad) {
                            selected.push(localidad.id_localidad);
                        });
                    }

                    this.selected = selected;
                }
            }
        }
    }
</script>

<style scoped>

</style>