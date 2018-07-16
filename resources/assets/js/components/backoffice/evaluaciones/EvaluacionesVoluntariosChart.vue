<template>
    <div>
        <div class="row">
            <div class="col-md-12">
                <h4>Puntajes recibidos</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="btn-group btn-group-justified" role="group" aria-label="gráfico de puntajes">
                    <a role="button" v-bind:class="getClass(btnPuntajeSocialClicked)" @click.prevent="verChartSocial">Puntaje Social</a>
                    <a role="button" v-bind:class="getClass(btnPuntajeTecnicoClicked)" @click.prevent="verChartTecnico">Puntaje Técnico</a>
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
            </div>
        </div>
    </div>
</template>

<script>
    import BarChart from '../../plugins/BarChart'
    import store from '../stores/store';

    export default {
        name: "evaluaciones-voluntarios-chart",
        components: { BarChart },
        data(){
            return {
                infoSocial: {},
                infoTecnico: {},
                btnPuntajeSocialClicked: true,
                btnPuntajeTecnicoClicked: false,
                options: {
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
            },
            verChartTecnico: function () {
                this.btnPuntajeSocialClicked = false;
                this.btnPuntajeTecnicoClicked = true;
            },
            getData: function () {
                let url = window.location.origin + "/admin/ajax/actividades/" + store.state.idActividad + "/evaluaciones/voluntarios/chartdata";
                this.axiosGet(
                    url,
                    //success callback
                    function (data, self) {
                        self.infoSocial = {
                            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                                datasets: [
                                    {
                                        label: 'Cantidad',
                                        backgroundColor: '#82CFE8',
                                        data: data.cantidadesSocial
                                    }
                                ]
                            };
                        self.infoTecnico = {
                            labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
                            datasets: [
                                {
                                    label: 'Cantidad',
                                    backgroundColor: '#82CFE8',
                                    data: data.cantidadesTecnico
                                }
                            ]
                        };
                        Event.$emit('chart-voluntarios-loaded');
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