<template>
    <div>
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center">
                        <div class="promedio-general">{{ promedio }}</div>
                        <div class="promedio-label">{{ $t('backend.average_general') }}</div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">{{ $t('backend.nps_score') }}</span>
                            <span class="nps-badge">{{ $t('backend.excellent') }} ({{ porcentajeExcelente }}%)</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" :style="{ width: porcentajeExcelente + '%' }"></div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-10 col-md-offset-1">
                        <evaluaciones-actividad-chart :id="id" :filtros="filtros"></evaluaciones-actividad-chart>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-center">
                <h5 class="text-center" style="margin-bottom: 6px;">{{ $t('backend.evaluation_status') }}</h5>
                <knob :valor="porcentajeCompletado"
                      :simbolo="'%'"
                      :listener="knobListener"
                ></knob>
                <div class="text-center mt-1">
                    <small>{{ $t('backend.completed') }}</small>
                </div>
                <div class="d-flex align-items-center mt-2 pt-2 border-top">
                    <i class="fa fa-user-circle mr-1" style="color: #5bc0de;"></i>
                    <span>{{ $t('backend.evaluated') }}</span>
                    <strong class="ml-auto">{{ evaluaron }}</strong>
                </div>
                <div class="d-flex align-items-center mt-2 pt-2 border-top">
                    <i class="fa fa-user-circle-o mr-1" style="color: #aaa;"></i>
                    <span>{{ $t('backend.pending_evaluation') }}</span>
                    <strong class="ml-auto">{{ pendientes }}</strong>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import knob from '../../plugins/knob';

    export default {
        name: "evaluaciones-actividad-stats",
        props: ['id', 'filtros'],
        components: { knob },
        data(){
            return {
                evaluaron: 0,
                promedio: 0,
                presentes: 0,
                porcentajeExcelente: 0,
                loading: true,
            }
        },
        computed: {
            knobListener() {
                return this.id ? 'knob-eval-actividad-upd' : 'knob-eval-general-upd';
            },
            porcentajeCompletado() {
                if (this.loading || this.presentes === 0) return 0;
                const p = Math.round(this.evaluaron * 100 / this.presentes);
                Event.$emit(this.knobListener, p);
                return p;
            },
            pendientes() {
                if (this.loading) return 0;
                return Math.max(0, this.presentes - this.evaluaron);
            },
            apiUrl() {
                return this.id
                    ? '/admin/ajax/actividades/' + this.id + '/evaluaciones/stats'
                    : '/admin/ajax/estadisticas/evaluaciones/actividad-stats';
            },
            apiParams() { return this.id ? {} : (this.filtros || {}); }
        },
        watch: {
            filtros: { deep: true, handler() { this.getStats(); } }
        },
        created(){
            this.getStats();
        },
        methods: {
            getStats() {
                this.loading = true;
                axios.get(this.apiUrl, { params: this.apiParams })
                    .then((datos) => {
                        this.evaluaron           = datos.data.evaluaron;
                        this.promedio            = datos.data.promedio;
                        this.presentes           = datos.data.presentes;
                        this.porcentajeExcelente = datos.data.porcentaje_excelente;
                        this.loading = false;
                        if (this.id) Event.$emit('stats-actividad-loaded');
                    });
            }
        }
    }
</script>

<style scoped>
.promedio-general {
    font-size: 52px;
    font-weight: bold;
    color: #00acc1;
    line-height: 1;
}
.promedio-label {
    font-size: 12px;
    color: #888;
    margin-top: 2px;
}
.nps-badge {
    font-size: 11px;
    color: #27ae60;
    font-weight: bold;
}
</style>
