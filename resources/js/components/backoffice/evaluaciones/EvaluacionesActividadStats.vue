<template>
    <div>
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-xs-4">
                        <div class="promedio-general">{{ promedio }}</div>
                        <div class="promedio-label">{{ $t('backend.average_general') }}</div>
                    </div>
                    <div class="col-xs-8">
                        <div class="nps-label-row">
                            <span class="nps-title">{{ $t('backend.nps_score') }}</span>
                            <span class="nps-badge">{{ $t('backend.excellent') }} ({{ porcentajeExcelente }}%)</span>
                        </div>
                        <div class="nps-bar-outer">
                            <div class="nps-bar-inner" :style="{ width: porcentajeExcelente + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="estado-circular-wrapper">
                    <h5 class="text-center" style="margin-bottom: 6px;">{{ $t('backend.evaluation_status') }}</h5>
                    <knob :valor="porcentajeCompletado"
                          :simbolo="'%'"
                          :listener="'knob-eval-actividad-upd'"
                    ></knob>
                    <div class="text-center" style="margin-top: 6px;">
                        <small>{{ $t('backend.completed') }}</small>
                    </div>
                    <div class="estado-row">
                        <i class="fa fa-user-circle" style="color: #5bc0de;"></i>
                        <span>{{ $t('backend.evaluated') }}</span>
                        <strong class="pull-right">{{ evaluaron }}</strong>
                    </div>
                    <div class="estado-row">
                        <i class="fa fa-user-circle-o" style="color: #aaa;"></i>
                        <span>{{ $t('backend.pending_evaluation') }}</span>
                        <strong class="pull-right">{{ pendientes }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import knob from '../../plugins/knob';

    export default {
        name: "evaluaciones-actividad-stats",
        props: ['id'],
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
            porcentajeCompletado() {
                if (this.loading || this.presentes === 0) return 0;
                const p = Math.round(this.evaluaron * 100 / this.presentes);
                Event.$emit('knob-eval-actividad-upd', p);
                return p;
            },
            pendientes() {
                if (this.loading) return 0;
                return Math.max(0, this.presentes - this.evaluaron);
            }
        },
        created(){
            this.getStats();
        },
        methods: {
            getStats() {
                axios.get("/admin/ajax/actividades/" + this.id + "/evaluaciones/stats")
                    .then((datos) => {
                        this.evaluaron           = datos.data.evaluaron;
                        this.promedio            = datos.data.promedio;
                        this.presentes           = datos.data.presentes;
                        this.porcentajeExcelente = datos.data.porcentaje_excelente;
                        this.loading = false;
                        Event.$emit('stats-actividad-loaded');
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
.nps-label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
    margin-top: 6px;
}
.nps-title {
    font-size: 12px;
    color: #555;
}
.nps-badge {
    font-size: 11px;
    color: #27ae60;
    font-weight: bold;
}
.nps-bar-outer {
    background: #e8f5e9;
    border-radius: 4px;
    height: 12px;
    width: 100%;
}
.nps-bar-inner {
    background: #27ae60;
    border-radius: 4px;
    height: 12px;
    transition: width 0.6s ease;
}
.estado-circular-wrapper {
    max-width: 200px;
    margin: 0 auto;
    text-align: center;
}
.estado-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 6px;
    font-size: 13px;
    border-top: 1px solid #f0f0f0;
    padding-top: 6px;
    text-align: left;
}
</style>
