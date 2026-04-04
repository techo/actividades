<template>
    <div v-if="comentarios.length > 0" style="margin-top: 20px;">
        <h5><i class="fa fa-comments-o"></i> {{ $t('backend.recent_comments') }}</h5>
        <div v-for="(item, index) in comentarios" :key="index" class="comentario-item">
            <div class="comentario-header">
                <span class="comentario-autor">{{ $t('backend.evaluators') }}</span>
                <span v-if="tienePositivos(item)" class="tag-chip tag-positivo">{{ $t('backend.positive') }}</span>
                <span v-else-if="tieneNegativos(item)" class="tag-chip tag-mejorable">{{ $t('backend.improvable') }}</span>
            </div>
            <p class="comentario-texto">"{{ item.comentario }}"</p>
        </div>
    </div>
</template>

<script>
    export default {
        name: "evaluaciones-comentarios",
        props: ['id'],
        data(){
            return {
                comentarios: [],
            }
        },
        created(){
            this.getData();
        },
        methods: {
            getData() {
                axios.get("/admin/ajax/actividades/" + this.id + "/evaluaciones/comentarios")
                    .then((res) => {
                        this.comentarios = res.data;
                    });
            },
            tienePositivos(item) {
                return item.tags_positivos && item.tags_positivos.length > 0;
            },
            tieneNegativos(item) {
                return item.tags_negativos && item.tags_negativos.length > 0;
            }
        }
    }
</script>

<style scoped>
.comentario-item {
    border-top: 1px solid #f0f0f0;
    padding: 10px 0;
}
.comentario-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}
.comentario-autor {
    font-weight: bold;
    font-size: 13px;
}
.comentario-texto {
    font-style: italic;
    color: #555;
    margin: 0;
    font-size: 13px;
}
.tag-chip {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: bold;
}
.tag-positivo {
    background: #e8f5e9;
    color: #27ae60;
}
.tag-mejorable {
    background: #fff8e1;
    color: #f39c12;
}
</style>
