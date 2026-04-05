<template>
    <div class="box">
        <div class="box-header with-border">
            <h4 class="box-title"><strong>{{ $t('backend.competencies_evaluation') }}</strong></h4>
            <span class="pull-right">
                <a class="btn btn-default btn-xs" :href="urlExportar">
                    <i class="fa fa-download"></i> {{ $t('backend.individual_report') }}
                </a>
            </span>
        </div>
        <div class="box-body">
            <div v-if="sinDatos" class="text-muted text-center" style="padding: 20px 0;">
                {{ $t('backend.no_competencies_yet') }}
            </div>
            <div v-else class="row">
                <div class="col-md-6 text-center">
                    <radar-chart v-if="chartData" :chart-data="chartData" :options="chartOptions" style="height:260px;"></radar-chart>
                </div>
                <div class="col-md-6">
                    <h5><strong>{{ $t('backend.dimensions_analysis') }}</strong></h5>
                    <p v-if="analisis" class="analisis-texto">
                        {{ $t('backend.group_highlights_in') }} <strong>{{ labelFor(analisis.mas_alto) }}</strong>,
                        {{ $t('backend.showing_strong_empathy') }}<br><br>
                        {{ $t('backend.opportunity_area') }}: <strong>{{ labelFor(analisis.mas_bajo) }}</strong>,
                        {{ $t('backend.lowest_point_with') }} {{ analisis.valor_bajo }}/10.
                    </p>
                    <div class="promedio-global-box">
                        <span class="promedio-global-label">{{ $t('backend.global_average') }}:</span>
                        <strong class="promedio-global-valor">{{ promedioGlobal }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import RadarChart from '../../plugins/RadarChart';

    const QUESTION_KEYS = [
        'conexion_equipo',
        'compromiso_colaboracion',
        'actitud_propositiva',
        'potencia_otras',
    ];

    export default {
        name: "evaluaciones-competencias",
        props: ['id', 'filtros'],
        components: { RadarChart },
        data(){
            return {
                promedios: {},
                promedioGlobal: null,
                analisis: null,
                sinDatos: false,
            }
        },
        computed: {
            apiUrl() {
                return this.id
                    ? '/admin/ajax/actividades/' + this.id + '/evaluaciones/competencias'
                    : '/admin/ajax/estadisticas/evaluaciones/competencias';
            },
            apiParams() { return this.id ? {} : (this.filtros || {}); },
            urlExportar() {
                if (this.id) return '/admin/actividades/' + this.id + '/exportar-evaluaciones-voluntarios';
                var qs = '';
                if (this.filtros) {
                    var parts = [];
                    if (this.filtros.año)     parts.push('año='     + this.filtros.año);
                    if (this.filtros.pais)    parts.push('pais='    + this.filtros.pais);
                    if (this.filtros.oficina) parts.push('oficina=' + this.filtros.oficina);
                    if (parts.length) qs = '?' + parts.join('&');
                }
                return '/admin/ajax/estadisticas/evaluaciones/exportar-personas' + qs;
            },
            radarLabels() {
                return [
                    this.$t('backend.competency_connection'),
                    this.$t('backend.competency_commitment'),
                    this.$t('backend.competency_attitude'),
                    this.$t('backend.competency_empowers'),
                ];
            },
            chartData() {
                const valores = QUESTION_KEYS.map(k => this.promedios[k] || 0);
                if (valores.every(v => v === 0)) return null;
                return {
                    labels: this.radarLabels,
                    datasets: [{
                        label: this.$t('backend.competencies_evaluation'),
                        data: valores,
                        backgroundColor: 'rgba(83, 166, 201, 0.2)',
                        borderColor: '#53a6c9',
                        pointBackgroundColor: '#53a6c9',
                        pointBorderColor: '#fff',
                    }]
                };
            },
            chartOptions() {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    scale: {
                        ticks: { beginAtZero: true, max: 10, min: 0, stepSize: 2 },
                        pointLabels: { fontSize: 11 }
                    },
                    legend: { display: false },
                };
            }
        },
        watch: {
            filtros: { deep: true, handler() { this.getData(); } }
        },
        created(){
            this.getData();
        },
        methods: {
            getData() {
                axios.get(this.apiUrl, { params: this.apiParams })
                    .then((res) => {
                        this.promedios      = res.data.promedios || {};
                        this.promedioGlobal = res.data.promedio_global;
                        this.analisis       = res.data.analisis;
                        const vals = Object.values(this.promedios).filter(function(v){ return v !== null; });
                        this.sinDatos = vals.length === 0;
                    });
            },
            labelFor(key) {
                const map = {
                    'conexion_equipo':         this.$t('backend.competency_connection'),
                    'compromiso_colaboracion':  this.$t('backend.competency_commitment'),
                    'actitud_propositiva':      this.$t('backend.competency_attitude'),
                    'potencia_otras':           this.$t('backend.competency_empowers'),
                };
                return map[key] || key;
            }
        }
    }
</script>

<style scoped>
.analisis-texto {
    font-size: 13px;
    color: #555;
    line-height: 1.6;
}
.promedio-global-box {
    margin-top: 16px;
    border-top: 1px solid #eee;
    padding-top: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.promedio-global-label {
    font-size: 13px;
    color: #888;
}
.promedio-global-valor {
    font-size: 22px;
    color: #333;
}
</style>
