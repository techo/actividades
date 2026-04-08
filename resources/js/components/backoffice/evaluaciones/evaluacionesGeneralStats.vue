<template>
    <div class="box-body">
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" style="min-height:90px; max-height:90px; overflow:hidden;">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                    <div class="info-box-content text-center d-flex flex-column justify-content-center overflow-hidden">
                        <div>
                            <strong style="font-size:30px; font-weight:700; line-height:1;">{{ inscriptos }}</strong>

                        </div>
                        <span class="d-block text-uppercase text-muted text-truncate" style="font-size:10px; letter-spacing:.6px; margin-top:4px;">{{ $t('backend.enrolled') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" style="min-height:90px; max-height:90px; overflow:hidden;">
                    <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                    <div class="info-box-content text-center d-flex flex-column justify-content-center overflow-hidden">
                        <div>
                        <strong style="font-size:30px; font-weight:700; line-height:1;">{{ presentes }}</strong>

                        </div>

                        <div>
                            <span class="d-block text-uppercase text-muted text-truncate" style="font-size:10px; letter-spacing:.6px; margin-top:4px;">{{ $t('backend.present') }}</span>
                        </div>
                        <span class="d-block text-truncate" v-if="inscriptos > 0" style="font-size:10px; color:#27ae60; font-weight:600; margin-top:2px;">{{ porcentajeAsistencia }} {{ $t('backend.attendance_percentage') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" style="min-height:90px; max-height:90px; overflow:hidden;">
                    <span class="info-box-icon bg-red"><i class="fa fa-times-circle"></i></span>
                    <div class="info-box-content text-center d-flex flex-column justify-content-center overflow-hidden">
                    <div>
                        <strong style="font-size:30px; font-weight:700; line-height:1;">{{ ausentes }}</strong>
                    </div>
                        <span class="d-block text-uppercase text-muted text-truncate" style="font-size:10px; letter-spacing:.6px; margin-top:4px;">{{ $t('backend.absent') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box" style="min-height:90px; max-height:90px; overflow:hidden;">
                    <span class="info-box-icon" style="background-color: #7e57c2;"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content text-center d-flex flex-column justify-content-center overflow-hidden">
                    <div>
                        <strong style="font-size:30px; font-weight:700; line-height:1;">{{ horasVoluntariado }}</strong>
                    </div>                        
                        <span class="d-block text-uppercase text-muted text-truncate" style="font-size:10px; letter-spacing:.6px; margin-top:4px;">{{ $t('backend.volunteer_hours') }}</span>
                        <span class="d-block text-truncate" v-if="horasPorVoluntario > 0" style="font-size:10px; color:#7e57c2; font-weight:600; margin-top:2px;">~{{ horasPorVoluntario }}{{ $t('backend.hours_per_volunteer') }}</span>
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

