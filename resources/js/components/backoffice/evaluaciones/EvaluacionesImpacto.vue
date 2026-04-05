<template>
    <div class="box">
        <div class="box-header with-border">
            <h4 class="box-title"><strong>{{ $t('backend.perceived_impact') }}</strong></h4>
            <span class="pull-right">
                <a class="btn btn-default btn-xs" :href="urlExportar">
                    <i class="fa fa-download"></i> {{ $t('backend.export') }}
                </a>
            </span>
        </div>
        <div class="box-body">
            <div v-if="total === 0" class="text-muted" style="font-size:13px;">{{ $t('backend.no_impact_yet') }}</div>
            <div v-else>
                <div class="impacto-row">
                    <div class="impacto-label-row">
                        <span class="impacto-label">{{ $t('backend.strengthened_skills') }}</span>
                        <span class="impacto-pct">{{ habilidades }}%</span>
                    </div>
                    <div class="impacto-bar-outer">
                        <div class="impacto-bar-inner" :style="{ width: habilidades + '%' }"></div>
                    </div>
                </div>
                <div class="impacto-row">
                    <div class="impacto-label-row">
                        <span class="impacto-label">{{ $t('backend.changed_perception') }}</span>
                        <span class="impacto-pct">{{ percepcion }}%</span>
                    </div>
                    <div class="impacto-bar-outer">
                        <div class="impacto-bar-inner" :style="{ width: percepcion + '%' }"></div>
                    </div>
                </div>
                <div class="impacto-row">
                    <div class="impacto-label-row">
                        <span class="impacto-label">{{ $t('backend.would_recommend') }}</span>
                        <span class="impacto-pct">{{ recomendaria }}%</span>
                    </div>
                    <div class="impacto-bar-outer">
                        <div class="impacto-bar-inner" :style="{ width: recomendaria + '%' }"></div>
                    </div>
                </div>
                <p class="impacto-footnote">{{ $t('backend.impact_footnote') }}</p>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "evaluaciones-impacto",
        props: ['id'],
        data(){
            return {
                total: 0,
                habilidades: 0,
                percepcion: 0,
                recomendaria: 0,
            }
        },
        computed: {
            urlExportar() {
                return "/admin/actividades/" + this.id + "/exportar-evaluaciones-impacto";
            }
        },
        created(){
            this.getData();
        },
        methods: {
            getData() {
                axios.get("/admin/ajax/actividades/" + this.id + "/evaluaciones/impacto")
                    .then((res) => {
                        this.total        = res.data.total;
                        this.habilidades  = res.data.habilidades;
                        this.percepcion   = res.data.percepcion;
                        this.recomendaria = res.data.recomendaria;
                    });
            }
        }
    }
</script>

<style scoped>
.impacto-row {
    margin-bottom: 14px;
}
.impacto-label-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
}
.impacto-label {
    font-size: 13px;
    color: #444;
}
.impacto-pct {
    font-size: 13px;
    font-weight: bold;
    color: #27ae60;
}
.impacto-bar-outer {
    background: #f5f5f5;
    border-radius: 4px;
    height: 10px;
}
.impacto-bar-inner {
    background: #27ae60;
    border-radius: 4px;
    height: 10px;
    transition: width 0.5s ease;
}
.impacto-footnote {
    font-size: 11px;
    color: #aaa;
    margin-top: 10px;
}
</style>
