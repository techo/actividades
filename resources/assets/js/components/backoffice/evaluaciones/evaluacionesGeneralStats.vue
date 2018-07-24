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
    import store from '../stores/store';

    export default {
        name: "evaluaciones-general-stats",
        computed: {
            inscriptos: function () {
                return store.state.inscriptos;
            },
            presentes: function () {
                return store.state.presentes;
            },
            ausentes: function () {
                return store.state.inscriptos - store.state.presentes;
            }
        },
        created(){
            this.getData();
            Event.$on('btnGrupoPersona:guardar-no-inscripto', this.getData);
            Event.$on('asistencia:cambio', this.getData);
        },
        methods:{
            getData: function () {
                let url = window.location.origin + "/admin/ajax/actividades/" + store.state.idActividad + "/evaluaciones/general/stats";
                this.axiosGet(url,
                //successCallback
                function (data, self) {
                    store.commit("updatePresentes", data.presentes);
                    store.commit("updateInscriptos", data.inscriptos);
                },
                    //payload
                    //errorCallback
                );
            }
        }
    }
</script>

<style scoped>

</style>