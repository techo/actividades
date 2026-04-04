<script>
    import { Radar, mixins } from 'vue-chartjs'
    export default {
        extends: Radar,
        name: "radar-chart",
        mixins: [mixins.reactiveProp],
        props: ['chartData', 'options'],
        mounted () {
            this.renderChart(this.chartData, this.options);
        }
    }
</script>
