<template>
    <div  v-if="link" ref="alerta" class="alert alert-info alert-dismissible" style="border-radius: 0px; margin-bottom: 0px;">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true" @click.prevent="cerrar" >×</button>
        <i class="icon fa fa-info-circle"></i>
        {{ texto }}
        &nbsp;
        <a class="" v-if="link" style="font-weight: bold;" :href="link" target="_blank" >Más info</a>
    </div>
</template>

<script>
    export default {
        name: "novedades",
        props: [],
        data: function () {
            return {
                texto: '',
                accion: '',
                link: '',
            }
        },
        mounted: function() {
            axios.get('/admin/novedades')
            .then((data) => {
                this.texto = data.data.texto;
                this.link = data.data.link;
            })
            .catch((error) => { debugger; });
        },
        methods: {
            cerrar: function () {
                this.$refs['alerta'].style.display = 'none';
                axios.get('/admin/novedades/visto').catch((error) => { debugger; });
                }
        }
    }
</script>

<style scoped>
</style>