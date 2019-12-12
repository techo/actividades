<template>
    <div>
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Información General</h3>
            </div>
            <div class="box-body">
                {{ actividad.nombreActividad }}
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "actividad",
        props: ['id'],
        components: {},
        data() {
            return {
                actividad: {},
            }
        },
        mounted() {
            Event.$on('guardar', this.guardar);

            axios.get('/admin/ajax/actividades/' + this.id)
                .then((datos) => { this.actividad = datos.data; })
                .catch((error) => { debugger; });
        },
        computed: {},
        filters: {},
        watch: {},
        methods: {
            guardar(){
                axios.post('/admin/ajax/actividades/' + this.id, this.actividad)
                    .then((datos) => { this.actividad = datos.data; })
                    .catch((error) => { debugger; });
            },
        }
    }
</script>

<style scoped>

</style>