<template>
    <div class="box">
        <div class="box-header with-border">
            <h4 class="box-title"><strong>Impacto Percibido</strong></h4>
        </div>
        <div class="box-body">
            <div v-if="total === 0" class="text-muted" style="font-size:13px;">Sin evaluaciones de impacto aún.</div>
            <div v-else>
                <div class="impacto-row">
                    <div class="impacto-label-row">
                        <span class="impacto-label">Fortalecí mis habilidades</span>
                        <span class="impacto-pct">{{ habilidades }}%</span>
                    </div>
                    <div class="impacto-bar-outer">
                        <div class="impacto-bar-inner" :style="{ width: habilidades + '%' }"></div>
                    </div>
                </div>
                <div class="impacto-row">
                    <div class="impacto-label-row">
                        <span class="impacto-label">Cambié mi percepción</span>
                        <span class="impacto-pct">{{ percepcion }}%</span>
                    </div>
                    <div class="impacto-bar-outer">
                        <div class="impacto-bar-inner" :style="{ width: percepcion + '%' }"></div>
                    </div>
                </div>
                <div class="impacto-row">
                    <div class="impacto-label-row">
                        <span class="impacto-label">Recomendaría experiencia</span>
                        <span class="impacto-pct">{{ recomendaria }}%</span>
                    </div>
                    <div class="impacto-bar-outer">
                        <div class="impacto-bar-inner" :style="{ width: recomendaria + '%' }"></div>
                    </div>
                </div>
                <p class="impacto-footnote">Porcentaje de participantes que respondieron 'Sí' o ≥ 8</p>
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
