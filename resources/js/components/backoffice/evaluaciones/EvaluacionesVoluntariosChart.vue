<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <h4>Evaluaciones registradas</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group btn-group-justified" role="group" aria-label="gráfico de puntajes">
                    <a role="button" v-bind:class="getClass(btnPuntajeSocialClicked)" @click.prevent="verChartSocial">Puntaje Social</a>
                    <a role="button" v-bind:class="getClass(btnPuntajeTecnicoClicked)" @click.prevent="verChartTecnico">Puntaje Técnico</a>
                    <a role="button" v-bind:class="getClass(btnPuntajeGeneroClicked)" @click.prevent="verChartGenero">Puntaje Género</a>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-12">
                <bar-chart
                        v-show="btnPuntajeSocialClicked"
                        :chart-data="infoSocial"
                        :options="options"
                        :height="200"
                ></bar-chart>
                <bar-chart
                        v-show="btnPuntajeTecnicoClicked"
                        :chart-data="infoTecnico"
                        :options="options"
                        :height="200"
                ></bar-chart>
                <bar-chart
                        v-show="btnPuntajeGeneroClicked"
                        :chart-data="infoGenero"
                        :options="options"
                        :height="200"
                ></bar-chart>
            </div>
        </div>
    </div>
</template>

<script>
    import BarChart from '../../plugins/BarChart'

    export default {
        name: "evaluaciones-voluntarios-chart",
        props: [ 'id' ],
        components: { BarChart },
        data(){
            return {
                infoSocial: {},
                infoTecnico: {},
                btnPuntajeSocialClicked: true,
                btnPuntajeTecnicoClicked: false,
                btnPuntajeGeneroClicked: false,
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
        mounted(){
            this.getData();
        },
        methods: {
            getClass: function (clicked) {
               let btnClass = clicked ? 'btn-primary' : 'btn-default';
              return {
                  'btn': true,
                  [btnClass] : true
              }
            },
            verChartSocial: function () {
                this.btnPuntajeSocialClicked = true;
                this.btnPuntajeTecnicoClicked = false;
                this.btnPuntajeGeneroClicked = false;
            },
            verChartTecnico: function () {
                this.btnPuntajeSocialClicked = false;
                this.btnPuntajeTecnicoClicked = true;
                this.btnPuntajeGeneroClicked = false;
            },
            verChartGenero: function () {
                this.btnPuntajeSocialClicked = false;
                this.btnPuntajeTecnicoClicked = false;
                this.btnPuntajeGeneroClicked = true;
            },
            getData: function () {
                axios.get("/admin/ajax/actividades/" + this.id + "/evaluaciones/voluntarios/chartdata")
                    .then((datos) => { 
                        this.infoSocial = {
                            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                                datasets: [
                                    {
                                        label: 'Cantidad',
                                        backgroundColor: '#82CFE8',
                                        data: datos.data.cantidadesSocial
                                    }
                                ]
                            };
                        this.infoTecnico = {
                            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                            datasets: [
                                {
                                    label: 'Cantidad',
                                    backgroundColor: '#82CFE8',
                                    data: datos.data.cantidadesTecnico
                                }
                            ]
                        };
                        this.infoGenero = {
                            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                            datasets: [
                                {
                                    label: 'Cantidad',
                                    backgroundColor: '#82CFE8',
                                    data: datos.data.cantidadesGenero
                                }
                            ]
                        };
                        Event.$emit('chart-voluntarios-loaded');
                    }).catch((error) => { debugger; });
            }
        }
    }
</script>

<style scoped>

</style>