import moment from 'moment'

/**
 * Mixin para tablas vuetable con columnas configurables.
 *
 * El componente que lo usa debe:
 *  - definir `listadoKey` (string, el list_key del registry config/listados.php),
 *  - tener `dataFields` en data (los fields que recibe su <vuetable>).
 *
 * Escucha el evento `columnas:aplicar:{listadoKey}` que emite
 * <column-selector-panel> y reemplaza dataFields por fijas + visibles.
 */
export default {
    created() {
        this._columnasHandler = payload => this.aplicarColumnas(payload)
        Event.$on(`columnas:aplicar:${this.listadoKey}`, this._columnasHandler)
    },
    beforeDestroy() {
        Event.$off(`columnas:aplicar:${this.listadoKey}`, this._columnasHandler)
    },
    methods: {
        aplicarColumnas({ fijas, camposVisibles }) {
            this.dataFields = [...fijas, ...camposVisibles].map(f => {
                const field = { ...f }
                if (field.title) {
                    field.title = this.translateField(field.title)
                }
                return field
            })
        },
        translateField(title) {
            if (!title) return title
            if (title.includes('.')) {
                return this.$t(title)
            }
            return title
        },
        // callback de la columna Edad (key: edad, name: fechaNacimiento)
        edad(value) {
            if (!value) return ''
            const anios = moment().diff(moment(value, 'YYYY-MM-DD'), 'years')
            return isNaN(anios) ? '' : anios
        },
    },
}
