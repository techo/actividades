<template>
    <div >
        <input type="checkbox" :value="propdatos.id_provincia" v-model="provincias_selected">
        <strong>{{ propdatos.provincia }}</strong>

        <div v-for="l in propdatos.localidades">
            <input type="checkbox" :value="l.id_localidad" v-model="localidades_selected">
            {{ l.localidad }}
        </div>
        <hr>
    </div>
</template>

<script>
    export default {
        name: "check-provincias",
        props: ['propdatos'],
        data() {
            return {
                localidades_selected: [],
                provincias_selected: [],
            }
        }
    }
</script>

<style scoped>

</style>