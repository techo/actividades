<template>
    <div class="box-body">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                    <div class="info-box-content text-center">
                        <span class="info-box-number"><h3>{{ inscriptos }}</h3></span>
                        <span class="info-box-text">{{ $t('backend.enrolled') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content text-center">
                        <span class="info-box-number"><h3>{{ presentes }}</h3></span>
                        <span class="info-box-text">{{ $t('backend.present') }}</span>
                        <span class="info-box-text" v-if="inscriptos > 0">{{ porcentajeAsistencia }}% {{ $t('backend.attendance_percentage') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-times-circle"></i></span>
                    <div class="info-box-content text-center">
                        <span class="info-box-number"><h3>{{ ausentes }}</h3></span>
                        <span class="info-box-text">{{ $t('backend.absent') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon" style="background-color: #7e57c2;"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content text-center">
                        <span class="info-box-number"><h3>{{ horasVoluntariado }}</h3></span>
                        <span class="info-box-text">{{ $t('backend.volunteer_hours') }}</span>
                        <span class="info-box-text" v-if="horasPorVoluntario > 0">~{{ horasPorVoluntario }}{{ $t('backend.hours_per_volunteer') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "evaluaciones-general-stats",
        props: ['id', 'filtros'],
        data(){
            return {
                inscriptos: 0,
                presentes: 0,
                ausentes: 0,
                horasVoluntariado: 0,
                horasPorVoluntario: 0,
            }
        },
        computed: {
            porcentajeAsistencia() {
                if (this.inscriptos === 0) return 0;
                return Math.round(this.presentes * 100 / this.inscriptos);
            },
            apiUrl() {
                return this.id
                    ? '/admin/ajax/actividades/' + this.id + '/evaluaciones/general/stats'
                    : '/admin/ajax/estadisticas/evaluaciones/general-stats';
            },
            apiParams() { return this.id ? {} : (this.filtros || {}); }
        },
        watch: {
            filtros: { deep: true, handler() { this.getData(); } }
        },
        created(){
            this.getData();
            if (this.id) {
                Event.$on('btnGrupoPersona:guardar-no-inscripto', this.getData);
                Event.$on('asistencia:cambio', this.getData);
            }
        },
        methods:{
            getData() {
                axios.get(this.apiUrl, { params: this.apiParams })
                    .then((datos) => {
                        this.presentes          = datos.data.presentes;
                        this.inscriptos         = datos.data.inscriptos;
                        this.ausentes           = datos.data.ausentes;
                        this.horasVoluntariado  = datos.data.horas_voluntariado;
                        this.horasPorVoluntario = datos.data.horas_por_voluntario;
                    });
            }
        }
    }
</script>
