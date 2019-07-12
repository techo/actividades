<script>
    import { Bar, mixins } from 'vue-chartjs'
    export default {
        extends: Bar,
        name: "bar-chart",
        mixins: [mixins.reactiveProp],
        props: ['chartData', 'options'],
        mounted () {
            this.renderChart(this.chartData, this.options);
        }
    }
</script>

<style scoped>

</style>