<template>
    <div>
        <!-- ── ROW 1: Stat Cards ── -->
        <div class="box-body" style="padding-bottom: 0;">
            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                        <div class="info-box-content text-center">
                            <span class="info-box-number"><h3>{{ generalStats.inscriptos }}</h3></span>
                            <span class="info-box-text">{{ $t('backend.enrolled') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-green"><i class="fa fa-check-circle"></i></span>
                        <div class="info-box-content text-center">
                            <span class="info-box-number"><h3>{{ generalStats.presentes }}</h3></span>
                            <span class="info-box-text">{{ $t('backend.present') }}</span>
                            <span class="info-box-text" v-if="generalStats.inscriptos > 0">{{ porcentajeAsistencia }}% {{ $t('backend.attendance_percentage') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-red"><i class="fa fa-times-circle"></i></span>
                        <div class="info-box-content text-center">
                            <span class="info-box-number"><h3>{{ generalStats.ausentes }}</h3></span>
                            <span class="info-box-text">{{ $t('backend.absent') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon" style="background-color: #7e57c2;"><i class="fa fa-clock-o"></i></span>
                        <div class="info-box-content text-center">
                            <span class="info-box-number"><h3>{{ generalStats.horas_voluntariado }}</h3></span>
                            <span class="info-box-text">{{ $t('backend.volunteer_hours') }}</span>
                            <span class="info-box-text" v-if="generalStats.horas_por_voluntario > 0">~{{ generalStats.horas_por_voluntario }}h {{ $t('backend.hours_per_volunteer') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── ROW 2: Evaluaciones de Actividad ── -->
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><strong>{{ $t('backend.activity_evaluations') }}</strong></h3>
                <span class="pull-right">
                    <a class="btn btn-primary btn-sm" :href="exportUrlActividad">
                        <i class="fa fa-download"></i> {{ $t('backend.download_excel') }}
                    </a>
                </span>
            </div>
            <div class="box-body">
                <!-- Stats: promedio + NPS | donut -->
                <div class="stats-flex-row">
                    <div class="stats-left">
                        <div class="promedio-block">
                            <div class="promedio-general">{{ actividadStats.promedio }}</div>
                            <div class="promedio-label">{{ $t('backend.average_general') }}</div>
                        </div>
                        <div class="nps-block">
                            <div class="nps-label-row">
                                <span class="nps-title">{{ $t('backend.nps_score') }}</span>
                                <span class="nps-badge">{{ $t('backend.excellent') }} ({{ actividadStats.porcentaje_excelente }}%)</span>
                            </div>
                            <div class="nps-bar-outer">
                                <div class="nps-bar-inner" :style="{ width: actividadStats.porcentaje_excelente + '%' }"></div>
                            </div>
                        </div>
                    </div>
                    <div class="stats-right">
                        <h5 class="text-center" style="margin-bottom: 6px;">{{ $t('backend.evaluation_status') }}</h5>
                        <knob :valor="porcentajeCompletado"
                              :simbolo="'%'"
                              :listener="'knob-eval-general-upd'"
                        ></knob>
                        <div class="text-center" style="margin-top: 6px;">
                            <small>{{ $t('backend.completed') }}</small>
                        </div>
                        <div class="estado-row">
                            <i class="fa fa-user-circle" style="color: #5bc0de;"></i>
                            <span>{{ $t('backend.evaluated') }}</span>
                            <strong style="margin-left: auto;">{{ actividadStats.evaluaron }}</strong>
                        </div>
                        <div class="estado-row">
                            <i class="fa fa-user-circle-o" style="color: #aaa;"></i>
                            <span>{{ $t('backend.pending_evaluation') }}</span>
                            <strong style="margin-left: auto;">{{ pendientes }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Histograma -->
                <div style="margin-top: 24px;" v-if="histogramaLoaded">
                    <h5>{{ $t('backend.registered_evaluations') }}</h5>
                    <bar-chart :chart-data="histogramaData" :options="histogramaOptions" :height="180"></bar-chart>
                </div>

                <!-- Comentarios -->
                <div v-if="comentarios.length > 0" style="margin-top: 20px;">
                    <h5><i class="fa fa-comments-o"></i> {{ $t('backend.recent_comments') }}</h5>
                    <div v-for="(item, index) in comentarios" :key="index" class="comentario-item">
                        <div class="comentario-header">
                            <span class="comentario-autor">{{ $t('backend.evaluators') }}</span>
                            <span v-if="item.tags_positivos && item.tags_positivos.length" class="tag-chip tag-positivo">{{ $t('backend.positive') }}</span>
                            <span v-else-if="item.tags_negativos && item.tags_negativos.length" class="tag-chip tag-mejorable">{{ $t('backend.improvable') }}</span>
                        </div>
                        <p class="comentario-texto">"{{ item.comentario }}"</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── ROW 3: Tags ── -->
        <div class="box">
            <div class="box-header with-border">
                <h4 class="box-title"><strong>{{ $t('backend.highlighted_attributes') }} &amp; {{ $t('backend.improvement_points') }}</strong></h4>
                <span class="pull-right">
                    <a class="btn btn-default btn-sm" :href="exportUrlPersonas">
                        <i class="fa fa-download"></i> {{ $t('backend.export') }} {{ $t('backend.competencies_evaluation') }}
                    </a>
                </span>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6" style="border-right: 1px solid #f0f0f0;">
                        <h4><strong>{{ $t('backend.highlighted_attributes') }}</strong></h4>
                        <p style="font-size:12px;color:#888;margin-bottom:14px;">{{ $t('backend.most_valued_by_volunteers') }}</p>
                        <div v-if="tags.positivos.length === 0" class="text-muted" style="font-size:13px;">{{ $t('backend.no_data_yet') }}</div>
                        <div v-for="item in tags.positivos" :key="item.key" class="tag-bar-row">
                            <div class="tag-bar-info">
                                <span style="color:#e53935;font-size:14px;">&#9829;</span>
                                <span class="tag-label">{{ item.label }}</span>
                                <span class="tag-count">{{ item.cantidad }} {{ $t('backend.votes') }}</span>
                            </div>
                            <div class="tag-bar-outer">
                                <div class="tag-bar-inner tag-bar-positiva" :style="{ width: item.porcentaje + '%' }"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4><strong>{{ $t('backend.improvement_points') }}</strong></h4>
                        <p style="font-size:12px;color:#888;margin-bottom:14px;">{{ $t('backend.low_evaluation_aspects') }}</p>
                        <div v-if="tags.negativos.length === 0" class="text-muted" style="font-size:13px;">{{ $t('backend.no_data_yet') }}</div>
                        <div v-for="item in tags.negativos" :key="item.key" class="tag-bar-row">
                            <div class="tag-bar-info">
                                <span style="color:#f39c12;font-size:14px;">&#9675;</span>
                                <span class="tag-label">{{ item.label }}</span>
                                <span class="tag-count">{{ item.cantidad }} {{ $t('backend.reports') }}</span>
                            </div>
                            <div class="tag-bar-outer">
                                <div class="tag-bar-inner tag-bar-negativa" :style="{ width: item.porcentaje + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── ROW 4: Competencias + Impacto ── -->
        <div class="row">
            <div class="col-md-7">
                <div class="box">
                    <div class="box-header with-border">
                        <h4 class="box-title"><strong>{{ $t('backend.competencies_evaluation') }}</strong></h4>
                    </div>
                    <div class="box-body">
                        <div v-if="competenciasSinDatos" class="text-muted text-center" style="padding:20px 0;">
                            {{ $t('backend.no_competencies_yet') }}
                        </div>
                        <div v-else class="row">
                            <div class="col-md-6 text-center">
                                <radar-chart v-if="radarData" :chart-data="radarData" :options="radarOptions" style="height:260px;"></radar-chart>
                            </div>
                            <div class="col-md-6">
                                <h5><strong>{{ $t('backend.dimensions_analysis') }}</strong></h5>
                                <p v-if="competencias.analisis" class="analisis-texto">
                                    {{ $t('backend.group_highlights_in') }} <strong>{{ labelFor(competencias.analisis.mas_alto) }}</strong>,
                                    {{ $t('backend.showing_strong_empathy') }}<br><br>
                                    {{ $t('backend.opportunity_area') }}: <strong>{{ labelFor(competencias.analisis.mas_bajo) }}</strong>,
                                    {{ $t('backend.lowest_point_with') }} {{ competencias.analisis.valor_bajo }}/10.
                                </p>
                                <div class="promedio-global-box">
                                    <span class="promedio-global-label">{{ $t('backend.global_average') }}:</span>
                                    <strong class="promedio-global-valor">{{ competencias.promedio_global }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="box">
                    <div class="box-header with-border">
                        <h4 class="box-title"><strong>{{ $t('backend.perceived_impact') }}</strong></h4>
                        <span class="pull-right">
                            <a class="btn btn-default btn-xs" :href="exportUrlImpacto">
                                <i class="fa fa-download"></i> {{ $t('backend.export') }}
                            </a>
                        </span>
                    </div>
                    <div class="box-body">
                        <div v-if="impacto.total === 0" class="text-muted" style="font-size:13px;">{{ $t('backend.no_impact_yet') }}</div>
                        <div v-else>
                            <div class="impacto-row">
                                <div class="impacto-label-row">
                                    <span class="impacto-label">{{ $t('backend.strengthened_skills') }}</span>
                                    <span class="impacto-pct">{{ impacto.habilidades }}%</span>
                                </div>
                                <div class="impacto-bar-outer">
                                    <div class="impacto-bar-inner" :style="{ width: impacto.habilidades + '%' }"></div>
                                </div>
                            </div>
                            <div class="impacto-row">
                                <div class="impacto-label-row">
                                    <span class="impacto-label">{{ $t('backend.changed_perception') }}</span>
                                    <span class="impacto-pct">{{ impacto.percepcion }}%</span>
                                </div>
                                <div class="impacto-bar-outer">
                                    <div class="impacto-bar-inner" :style="{ width: impacto.percepcion + '%' }"></div>
                                </div>
                            </div>
                            <div class="impacto-row">
                                <div class="impacto-label-row">
                                    <span class="impacto-label">{{ $t('backend.would_recommend') }}</span>
                                    <span class="impacto-pct">{{ impacto.recomendaria }}%</span>
                                </div>
                                <div class="impacto-bar-outer">
                                    <div class="impacto-bar-inner" :style="{ width: impacto.recomendaria + '%' }"></div>
                                </div>
                            </div>
                            <p class="impacto-footnote">{{ $t('backend.impact_footnote') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import BarChart   from '../../plugins/BarChart';
    import RadarChart from '../../plugins/RadarChart';
    import knob       from '../../plugins/knob';

    const QUESTION_KEYS = ['conexion_equipo', 'compromiso_colaboracion', 'actitud_propositiva', 'potencia_otras'];

    export default {
        name: 'evaluaciones-dashboard-general',
        props: ['filtros'],
        components: { BarChart, RadarChart, knob },

        data() {
            return {
                generalStats: { inscriptos: 0, presentes: 0, ausentes: 0, horas_voluntariado: 0, horas_por_voluntario: 0 },
                actividadStats: { promedio: 0, porcentaje_excelente: 0, evaluaron: 0, presentes: 0 },
                histogramaData: {},
                histogramaLoaded: false,
                comentarios: [],
                tags: { positivos: [], negativos: [] },
                competencias: { promedios: {}, promedio_global: null, analisis: null },
                impacto: { total: 0, habilidades: 0, percepcion: 0, recomendaria: 0 },
                histogramaOptions: {
                    scales: {
                        yAxes: [{
                            scaleLabel: { display: true, labelString: 'Total' },
                            ticks: {
                                beginAtZero: true,
                                userCallback: function(label) {
                                    if (Math.floor(label) === label) return label;
                                }
                            }
                        }],
                        xAxes: [{ scaleLabel: { display: true, labelString: 'Puntaje' } }]
                    },
                    responsive: true,
                    maintainAspectRatio: true,
                    legend: { display: false }
                },
                radarOptions: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scale: {
                        ticks: { beginAtZero: true, max: 10, min: 0, stepSize: 2 },
                        pointLabels: { fontSize: 11 }
                    },
                    legend: { display: false }
                },
            };
        },

        computed: {
            porcentajeAsistencia() {
                if (!this.generalStats.inscriptos) return 0;
                return Math.round(this.generalStats.presentes * 100 / this.generalStats.inscriptos);
            },
            porcentajeCompletado() {
                if (!this.actividadStats.presentes) return 0;
                const p = Math.round(this.actividadStats.evaluaron * 100 / this.actividadStats.presentes);
                Event.$emit('knob-eval-general-upd', p);
                return p;
            },
            pendientes() {
                return Math.max(0, this.actividadStats.presentes - this.actividadStats.evaluaron);
            },
            competenciasSinDatos() {
                const vals = Object.values(this.competencias.promedios).filter(function(v) { return v !== null; });
                return vals.length === 0;
            },
            radarLabels() {
                return [
                    this.$t('backend.competency_connection'),
                    this.$t('backend.competency_commitment'),
                    this.$t('backend.competency_attitude'),
                    this.$t('backend.competency_empowers'),
                ];
            },
            radarData() {
                const valores = QUESTION_KEYS.map(function(k) {
                    return this.competencias.promedios[k] || 0;
                }.bind(this));
                if (valores.every(function(v) { return v === 0; })) return null;
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
            queryString() {
                var parts = [];
                if (this.filtros && this.filtros.año)     parts.push('año='     + this.filtros.año);
                if (this.filtros && this.filtros.pais)    parts.push('pais='    + this.filtros.pais);
                if (this.filtros && this.filtros.oficina) parts.push('oficina=' + this.filtros.oficina);
                return parts.length ? '?' + parts.join('&') : '';
            },
            exportUrlActividad() {
                return '/admin/ajax/estadisticas/evaluaciones/exportar-actividad' + this.queryString;
            },
            exportUrlPersonas() {
                return '/admin/ajax/estadisticas/evaluaciones/exportar-personas' + this.queryString;
            },
            exportUrlImpacto() {
                return '/admin/ajax/estadisticas/evaluaciones/exportar-impacto' + this.queryString;
            },
        },

        watch: {
            filtros: {
                deep: true,
                handler: function() { this.loadAll(); }
            }
        },

        mounted() {
            this.loadAll();
        },

        methods: {
            params() {
                return { params: this.filtros || {} };
            },
            loadAll() {
                this.loadGeneralStats();
                this.loadActividadStats();
                this.loadHistograma();
                this.loadComentarios();
                this.loadTags();
                this.loadCompetencias();
                this.loadImpacto();
            },
            loadGeneralStats() {
                axios.get('/admin/ajax/estadisticas/evaluaciones/general-stats', this.params())
                    .then(function(res) { this.generalStats = res.data; }.bind(this));
            },
            loadActividadStats() {
                axios.get('/admin/ajax/estadisticas/evaluaciones/actividad-stats', this.params())
                    .then(function(res) { this.actividadStats = res.data; }.bind(this));
            },
            loadHistograma() {
                this.histogramaLoaded = false;
                axios.get('/admin/ajax/estadisticas/evaluaciones/histograma', this.params())
                    .then(function(res) {
                        this.histogramaData = {
                            labels: ['1','2','3','4','5','6','7','8','9','10'],
                            datasets: [{
                                label: 'Total',
                                backgroundColor: '#82CFE8',
                                data: res.data.cantidades
                            }]
                        };
                        this.histogramaLoaded = true;
                    }.bind(this));
            },
            loadComentarios() {
                axios.get('/admin/ajax/estadisticas/evaluaciones/comentarios', this.params())
                    .then(function(res) { this.comentarios = res.data; }.bind(this));
            },
            loadTags() {
                axios.get('/admin/ajax/estadisticas/evaluaciones/tags', this.params())
                    .then(function(res) {
                        this.tags.positivos = res.data.positivos || [];
                        this.tags.negativos = res.data.negativos || [];
                    }.bind(this));
            },
            loadCompetencias() {
                axios.get('/admin/ajax/estadisticas/evaluaciones/competencias', this.params())
                    .then(function(res) { this.competencias = res.data; }.bind(this));
            },
            loadImpacto() {
                axios.get('/admin/ajax/estadisticas/evaluaciones/impacto', this.params())
                    .then(function(res) { this.impacto = res.data; }.bind(this));
            },
            labelFor(key) {
                var map = {
                    'conexion_equipo':        this.$t('backend.competency_connection'),
                    'compromiso_colaboracion': this.$t('backend.competency_commitment'),
                    'actitud_propositiva':     this.$t('backend.competency_attitude'),
                    'potencia_otras':          this.$t('backend.competency_empowers'),
                };
                return map[key] || key;
            }
        }
    }
</script>

<style scoped>
/* stat cards */
.stats-flex-row { display: flex; align-items: center; gap: 24px; }
.stats-left     { flex: 1; display: flex; align-items: center; gap: 24px; }
.stats-right    { width: 220px; flex-shrink: 0; text-align: center; }
.promedio-block { flex-shrink: 0; }
.nps-block      { flex: 1; }
.promedio-general { font-size: 52px; font-weight: bold; color: #00acc1; line-height: 1; }
.promedio-label   { font-size: 12px; color: #888; margin-top: 2px; }
.nps-label-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; }
.nps-title  { font-size: 12px; color: #555; }
.nps-badge  { font-size: 11px; color: #27ae60; font-weight: bold; }
.nps-bar-outer { background: #e8f5e9; border-radius: 4px; height: 12px; width: 100%; }
.nps-bar-inner { background: #27ae60; border-radius: 4px; height: 12px; transition: width 0.6s ease; }
.estado-row { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 13px; border-top: 1px solid #f0f0f0; padding-top: 6px; }

/* comentarios */
.comentario-item   { border-top: 1px solid #f0f0f0; padding: 10px 0; }
.comentario-header { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.comentario-autor  { font-weight: bold; font-size: 13px; }
.comentario-texto  { font-style: italic; color: #555; margin: 0; font-size: 13px; }
.tag-chip     { font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: bold; }
.tag-positivo { background: #e8f5e9; color: #27ae60; }
.tag-mejorable{ background: #fff8e1; color: #f39c12; }

/* tags */
.tag-bar-row  { margin-bottom: 12px; }
.tag-bar-info { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
.tag-label    { flex: 1; font-size: 13px; }
.tag-count    { font-size: 12px; color: #888; white-space: nowrap; }
.tag-bar-outer    { background: #f5f5f5; border-radius: 4px; height: 8px; }
.tag-bar-inner    { border-radius: 4px; height: 8px; transition: width 0.5s ease; }
.tag-bar-positiva { background: #26c6da; }
.tag-bar-negativa { background: #ffa726; }

/* competencias */
.analisis-texto { font-size: 13px; color: #555; line-height: 1.6; }
.promedio-global-box   { margin-top: 16px; border-top: 1px solid #eee; padding-top: 10px; display: flex; justify-content: space-between; align-items: center; }
.promedio-global-label { font-size: 13px; color: #888; }
.promedio-global-valor { font-size: 22px; color: #333; }

/* impacto */
.impacto-row      { margin-bottom: 14px; }
.impacto-label-row{ display: flex; justify-content: space-between; margin-bottom: 4px; }
.impacto-label    { font-size: 13px; color: #444; }
.impacto-pct      { font-size: 13px; font-weight: bold; color: #27ae60; }
.impacto-bar-outer{ background: #f5f5f5; border-radius: 4px; height: 10px; }
.impacto-bar-inner{ background: #27ae60; border-radius: 4px; height: 10px; transition: width 0.5s ease; }
.impacto-footnote { font-size: 11px; color: #aaa; margin-top: 10px; }
</style>
