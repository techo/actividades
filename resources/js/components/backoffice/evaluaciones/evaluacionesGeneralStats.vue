<template>
    <div class="box-body">
        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>

                    <div class="info-box-content text-center">
                        <span class="info-box-number"><h3>{{ this.inscriptos }}</h3></span>
                        <span class="info-box-text">Inscriptos</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>

                    <div class="info-box-content text-center">
                        <span class="info-box-number"><h3>{{ this.presentes }}</h3></span>
                        <span class="info-box-text">Presentes</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
            </div>
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-times-circle"></i></span>

                    <div class="info-box-content text-center">
                        <span class="info-box-number"><h3>{{ this.ausentes }}</h3></span>
                        <span class="info-box-text">Ausentes</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</template>

<script>
    export default {
        name: "evaluaciones-general-stats",
        props: ['id'],
        data(){
            return {
                inscriptos: 0,
                presentes: 0,
            }
        },
        computed: {
            ausentes: function () {
                return this.inscriptos-this.presentes;
            }
        },
        created(){
            this.getData();
            Event.$on('btnGrupoPersona:guardar-no-inscripto', this.getData);
            Event.$on('asistencia:cambio', this.getData);
        },
        methods:{
            getData: function () {
                axios.get("/admin/ajax/actividades/" + this.id + "/evaluaciones/general/stats")
                    .then((datos) => { 
                        this.presentes = datos.data.presentes; 
                        this.inscriptos = datos.data.inscriptos; 
                    }).catch((error) => { debugger; });
            }
        }
    }
</script>

<style scoped>

</style>