<template>
    <div class="box">
        <div class="box-body">
            <div class="row">
                <!-- Atributos Destacados -->
                <div class="col-md-6 col-tags-positivos">
                    <h4 class="tags-section-title"><strong>Atributos Destacados</strong></h4>
                    <p class="tags-section-sub">Lo más valorado por los voluntarios.</p>
                    <div v-if="positivos.length === 0" class="text-muted" style="font-size:13px;">Sin datos aún</div>
                    <div v-for="item in positivos" :key="item.key" class="tag-bar-row">
                        <div class="tag-bar-info">
                            <span class="tag-heart">&#9829;</span>
                            <span class="tag-label">{{ labelFor(item.key) }}</span>
                            <span class="tag-count">{{ item.cantidad }} votos</span>
                        </div>
                        <div class="tag-bar-outer">
                            <div class="tag-bar-inner tag-bar-positiva" :style="{ width: item.porcentaje + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Puntos de Mejora -->
                <div class="col-md-6 col-tags-negativos">
                    <h4 class="tags-section-title"><strong>Puntos de Mejora</strong></h4>
                    <p class="tags-section-sub">Aspectos con evaluación menor a 7.</p>
                    <div v-if="negativos.length === 0" class="text-muted" style="font-size:13px;">Sin datos aún</div>
                    <div v-for="item in negativos" :key="item.key" class="tag-bar-row">
                        <div class="tag-bar-info">
                            <span class="tag-warn">&#9675;</span>
                            <span class="tag-label">{{ labelForNegativo(item.key) }}</span>
                            <span class="tag-count">{{ item.cantidad }} reportes</span>
                        </div>
                        <div class="tag-bar-outer">
                            <div class="tag-bar-inner tag-bar-negativa" :style="{ width: item.porcentaje + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    const LABELS_POSITIVOS = {
        informacion_previa:     'Información previa clara y útil',
        ambiente_seguro:        'Ambiente seguro y agradable',
        reflexion_inspiradora:  'Reflexión inspiradora y fortalecedora',
        buena_logistica:        'Buena logística y alimentación',
        conexion_comunidad:     'Conexión con familia o comunidad',
        liderazgos_proactivos:  'Liderazgos proactivos y atentos',
        herramientas_completas: 'Herramientas e insumos completos',
        motivacion_energia:     'Sentir motivación y energía',
    };

    const LABELS_NEGATIVOS = {
        poca_informacion:        'Poca información previa',
        falta_conexion:          'Falta de conexión con comunidad',
        herramientas_incompletas:'Herramientas o insumos incompletos',
        no_conecte_causa:        'No conseguí conectarme con la causa',
        incomodidad:             'Sentí incomodidad o inseguridad',
        poca_presencia_staff:    'Poca presencia o acompañamiento del staff',
        logistica_insuficiente:  'Logística o alimentación insuficiente',
    };

    export default {
        name: "evaluaciones-tags-resumen",
        props: ['id'],
        data(){
            return {
                positivos: [],
                negativos: [],
            }
        },
        created(){
            this.getData();
        },
        methods: {
            getData() {
                axios.get("/admin/ajax/actividades/" + this.id + "/evaluaciones/tags")
                    .then((res) => {
                        this.positivos = res.data.positivos || [];
                        this.negativos = res.data.negativos || [];
                    });
            },
            labelFor(key) {
                return LABELS_POSITIVOS[key] || key;
            },
            labelForNegativo(key) {
                return LABELS_NEGATIVOS[key] || key;
            }
        }
    }
</script>

<style scoped>
.col-tags-positivos {
    border-right: 1px solid #f0f0f0;
}
.tags-section-title {
    margin-bottom: 2px;
}
.tags-section-sub {
    font-size: 12px;
    color: #888;
    margin-bottom: 14px;
}
.tag-bar-row {
    margin-bottom: 12px;
}
.tag-bar-info {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}
.tag-label {
    flex: 1;
    font-size: 13px;
}
.tag-count {
    font-size: 12px;
    color: #888;
    white-space: nowrap;
}
.tag-heart {
    color: #e53935;
    font-size: 14px;
}
.tag-warn {
    color: #f39c12;
    font-size: 14px;
}
.tag-bar-outer {
    background: #f5f5f5;
    border-radius: 4px;
    height: 8px;
}
.tag-bar-inner {
    border-radius: 4px;
    height: 8px;
    transition: width 0.5s ease;
}
.tag-bar-positiva {
    background: #26c6da;
}
.tag-bar-negativa {
    background: #ffa726;
}
</style>
