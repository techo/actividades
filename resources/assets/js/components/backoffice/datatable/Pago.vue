<template>
    <span>
        <v-switch
                v-model="pago"
                theme="bootstrap"
                color="primary"
                id="pago"
                name="pago"
                type-bold="true"
                text-enabled="Pagado"
                text-disabled="Pendiente"
        >
        </v-switch>
    </span>
</template>

<script>
    import vSwitch from 'vue-switches';

    export default {
        name: "pago",
        components: {'v-switch': vSwitch},
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                type: Number
            }
        },
        data() {
            return {
                pago: this.rowData.pago,
                idInscripcion: this.rowData.id,
            }
        },
        created() {
        },
        computed: {},
        methods: {},
        watch: {
            pago() {
                Event.$emit('pago_' + this.idInscripcion, this.pago);
            },
            'rowData.pago': function () {
                this.pago = (this.rowData.pago === null) ? 0 : this.rowData.pago;
            }

        }
    }
</script>

<style scoped>

</style>