<template>
    <div class="box">
        <div class="box-header with-border">
            <h4 class="box-title"><strong>Evaluación de Competencias (Participantes)</strong></h4>
            <span class="pull-right">
                <a class="btn btn-default btn-xs" :href="urlExportar">
                    <i class="fa fa-download"></i> Informe Individual
                </a>
            </span>
        </div>
        <div class="box-body">
            <div v-if="sinDatos" class="text-muted text-center" style="padding: 20px 0;">
                Sin evaluaciones de competencias aún.
            </div>
            <div v-else class="row">
                <div class="col-md-6 text-center">
                    <radar-chart v-if="chartData" :chart-data="chartData" :options="chartOptions" style="height:260px;"></radar-chart>
                </div>
                <div class="col-md-6">
                    <h5><strong>Análisis de Dimensiones</strong></h5>
                    <p v-if="analisis" class="analisis-texto">
                        El grupo destaca en <strong>{{ labelFor(analisis.mas_alto) }}</strong>,
                        mostrando una fuerte empatía con la comunidad.<br><br>
                        Área de oportunidad: <strong>{{ labelFor(analisis.mas_bajo) }}</strong>,
                        es el punto más bajo con {{ analisis.valor_bajo }}/10.
                    </p>
                    <div class="promedio-global-box">
                        <span class="promedio-global-label">Promedio Global:</span>
                        <strong class="promedio-global-valor">{{ promedioGlobal }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import RadarChart from '../../plugins/RadarChart';

    const LABELS = {
        conexion_equipo:         'Conexión y Comunidad',
        compromiso_colaboracion: 'Compromiso y Colaboración',
        actitud_propositiva:     'Actitud Propositiva',
        potencia_otras:          'Potencia a Otros',
    };

    const QUESTION_KEYS = [
        'conexion_equipo',
        'compromiso_colaboracion',
        'actitud_propositiva',
        'potencia_otras',
    ];

    export default {
        name: "evaluaciones-competencias",
        props: ['id'],
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
            urlExportar() {
                return "/admin/actividades/" + this.id + "/exportar-evaluaciones-voluntarios";
            },
            chartData() {
                const valores = QUESTION_KEYS.map(k => this.promedios[k] || 0);
                if (valores.every(v => v === 0)) return null;
                return {
                    labels: QUESTION_KEYS.map(k => LABELS[k]),
                    datasets: [{
                        label: 'Competencias',
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
        created(){
            this.getData();
        },
        methods: {
            getData() {
                axios.get("/admin/ajax/actividades/" + this.id + "/evaluaciones/competencias")
                    .then((res) => {
                        this.promedios      = res.data.promedios || {};
                        this.promedioGlobal = res.data.promedio_global;
                        this.analisis       = res.data.analisis;
                        const vals = Object.values(this.promedios).filter(v => v !== null);
                        this.sinDatos = vals.length === 0;
                    });
            },
            labelFor(key) {
                return LABELS[key] || key;
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
