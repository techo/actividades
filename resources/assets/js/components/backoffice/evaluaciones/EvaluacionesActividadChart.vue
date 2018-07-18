<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <h4>Puntajes recibidos</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <bar-chart
                        :chart-data="info"
                        :options="options"
                        :height="200"
                ></bar-chart>
            </div>
        </div>
    </div>
</template>

<script>
    import BarChart from '../../plugins/BarChart'
    import store from '../stores/store';

    export default {
        name: "evaluaciones-actividad-chart",
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
        created(){
            this.getData();
        },
        methods: {
            getData: function () {
                let url = window.location.origin + "/admin/ajax/actividades/" + store.state.idActividad + "/evaluaciones/chartdata";
                this.axiosGet(
                    url,
                    //success callback
                    function (data, self) {
                        self.info = {
                            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                                datasets: [
                                    {
                                        label: 'Cantidad',
                                        backgroundColor: '#82CFE8',
                                        data: data.cantidades
                                    }
                                ]
                            };
                        Event.$emit('chart-actividad-loaded');
                    }
                    //payload
                    //error callback
                );
            }
        }
    }
</script>

<style scoped>

</style>