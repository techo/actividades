<template>
    <div class="box">
        <div class="box-body">
            <div class="row">
                <!-- Atributos Destacados -->
                <div class="col-md-6 col-tags-positivos">
                    <h4 class="tags-section-title"><strong>{{ $t('backend.highlighted_attributes') }}</strong></h4>
                    <p class="tags-section-sub">{{ $t('backend.most_valued_by_volunteers') }}</p>
                    <div v-if="positivos.length === 0" class="text-muted" style="font-size:13px;">{{ $t('backend.no_data_yet') }}</div>
                    <div v-for="item in positivos" :key="item.key" class="tag-bar-row">
                        <div class="tag-bar-info">
                            <span class="tag-heart">&#9829;</span>
                            <span class="tag-label">{{ item.label }}</span>
                            <span class="tag-count">{{ item.cantidad }} {{ $t('backend.votes') }}</span>
                        </div>
                        <div class="tag-bar-outer">
                            <div class="tag-bar-inner tag-bar-positiva" :style="{ width: item.porcentaje + '%' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Puntos de Mejora -->
                <div class="col-md-6 col-tags-negativos">
                    <h4 class="tags-section-title"><strong>{{ $t('backend.improvement_points') }}</strong></h4>
                    <p class="tags-section-sub">{{ $t('backend.low_evaluation_aspects') }}</p>
                    <div v-if="negativos.length === 0" class="text-muted" style="font-size:13px;">{{ $t('backend.no_data_yet') }}</div>
                    <div v-for="item in negativos" :key="item.key" class="tag-bar-row">
                        <div class="tag-bar-info">
                            <span class="tag-warn">&#9675;</span>
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
</template>

<script>
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
