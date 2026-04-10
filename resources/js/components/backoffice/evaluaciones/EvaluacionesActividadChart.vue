<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <bar-chart
                        :chart-data="info"
                        :options="options"
                        :height="140"
                ></bar-chart>
            </div>
        </div>
    </div>
</template>

<script>
    import BarChart from '../../plugins/BarChart'

    export default {
        name: "evaluaciones-actividad-chart",
        props: ['id', 'filtros'],
        components: { BarChart },
        data(){
            return {
                info: {},
                options: {
                    scales: {
                        yAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: "Total"
                            },
                            ticks: {
                                beginAtZero: true,
                                userCallback: function(label, index, labels) {
                                    // Si el entero del label es igual al label, mostrar
                                    if (Math.floor(label) === label) {
                                        return label;
                                    }

                                },
                            }
                        }],
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: "Evaluación"
                            }
                        }]
                    },
                    responsive: true,
                    maintainAspectRatio: true,
                    legend: {
                        display: false
                    }
                }
            }
        },
        computed: {
            apiUrl() {
                return this.id
                    ? '/admin/ajax/actividades/' + this.id + '/evaluaciones/chartdata'
                    : '/admin/ajax/estadisticas/evaluaciones/histograma';
            },
            apiParams() { return this.id ? {} : (this.filtros || {}); }
        },
        watch: {
            filtros: { deep: true, handler() { this.getData(); } }
        },
        created(){
            this.getData();
        },
        methods: {
            getData: function () {
                axios.get(this.apiUrl, { params: this.apiParams })
                    .then((response) => {
                        this.info = {
                            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                            datasets: [{
                                label: 'Cantidad',
                                backgroundColor: '#82CFE8',
                                data: response.data.cantidades
                            }]
                        };
                        if (this.id) Event.$emit('chart-actividad-loaded');
                    });
            }
        }
    }
</script>

<style scoped>

</style>