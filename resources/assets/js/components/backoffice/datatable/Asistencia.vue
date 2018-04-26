<template>
    <span>
        <v-switch
                v-model="presente"
                theme="bootstrap"
                color="primary"
                id="pago_"
                name="pago"
                type-bold="true"
                text-enabled="Presente"
                text-disabled="Ausente"
        >
        </v-switch>
    </span>
</template>

<script>
    export default {
        name: "asistencia",
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
                presente: this.rowData.presente,
                idInscripcion: this.rowData.id
            }
        },
        created() {
        },
        computed: {},
        methods: {},
        watch: {
            presente() {
                Event.$emit('asistencia_' + this.idInscripcion, this.presente);
            },
            'rowData.presente': function () {
                this.presente = (this.rowData.presente === null) ? 0 : this.rowData.presente;
            }
        }
    }
</script>

<style scoped>

</style>