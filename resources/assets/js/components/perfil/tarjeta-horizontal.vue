<template>
    <div class="row">
        <div class="col-md-3">
            <img :src="rowData.img" alt="rowData.tipo" width="120">
        </div>
        <div class="col-md-9">
            <div class="small-caps">
                {{ rowData.tipo }}
            </div>
            <div><a :href="urlDetalle">{{ rowData.nombreActividad }}</a></div>

            <span class="small-text"><i class="fas fa-calendar-alt"></i> {{ rowData.fecha }}</span>
            <span class="small-text"><i class="fas fa-clock"></i> {{ rowData.hora }}</span>
            <span class="small-text"><i class="fas fa-map-marker-alt"></i> {{ rowData.lugar }}</span>

        </div>
    </div>
</template>

<script>
export default {
    name: "tarjeta-horizontal",
    props: {
        rowData: {
            type: Object,
            required: true
        },
        rowIndex: {
            type: Number
        }
    },
    data: function () {
        return {
            idActividad: '',
            //urlDetalle: '/actividades/' + this.rowData.idActividad
        }
    },
    watch: {
        'rowData.idActividad': function(nuevo) {
            this.idActividad = nuevo;
        }
    },
    computed: {
        urlDetalle: function () {
            return '/actividades/' + this.idActividad;
        }
    }
}
</script>

<style scoped>
    .small-caps {
        font-size: smaller;
        font-variant: all-petite-caps;
    }
    .small-text {
        font-size: smaller;
    }

    span.small-text {
        margin-right: 10px;
    }
</style>