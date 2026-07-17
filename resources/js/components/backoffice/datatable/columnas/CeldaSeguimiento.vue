<template>
    <div class="celda-seguimiento" @click.stop>
        <!-- casilla -->
        <input
            v-if="tipo === 'casilla'"
            type="checkbox"
            :checked="valor === '1'"
            :disabled="guardando"
            @change="guardar($event.target.checked ? '1' : null)"
        >

        <!-- estado -->
        <select
            v-else-if="tipo === 'estado'"
            class="form-control input-sm"
            :value="valor || ''"
            :disabled="guardando"
            @change="guardar($event.target.value || null)"
        >
            <option value=""></option>
            <option v-for="opcion in meta.opciones" :key="opcion" :value="opcion">{{ opcion }}</option>
        </select>

        <!-- etiquetas -->
        <div v-else-if="tipo === 'etiquetas'" class="dropdown" :class="{ open: abierto }">
            <a href="#" @click.prevent="abierto = !abierto">
                <template v-if="etiquetas.length">
                    <span v-for="etiqueta in etiquetas" :key="etiqueta" class="label label-default">
                        {{ etiqueta }}
                    </span>
                </template>
                <span v-else class="text-muted">—</span>
            </a>
            <ul class="dropdown-menu" @click.stop>
                <li v-for="opcion in meta.opciones" :key="opcion">
                    <a href="#" @click.prevent="toggleEtiqueta(opcion)">
                        <input type="checkbox" :checked="etiquetas.includes(opcion)" @click.stop.prevent>
                        {{ opcion }}
                    </a>
                </li>
            </ul>
        </div>

        <!-- texto -->
        <div v-else-if="tipo === 'texto'">
            <input
                v-if="editando"
                ref="inputTexto"
                type="text"
                class="form-control input-sm"
                maxlength="1000"
                v-model="borrador"
                @blur="confirmarTexto"
                @keyup.enter="$event.target.blur()"
                @keyup.esc="editando = false"
            >
            <a v-else href="#" @click.prevent="editarTexto">
                <span v-if="valor">{{ valor }}</span>
                <span v-else class="text-muted">—</span>
            </a>
        </div>

        <!-- fecha -->
        <input
            v-else-if="tipo === 'fecha'"
            type="date"
            class="form-control input-sm"
            :value="valor || ''"
            :disabled="guardando"
            @change="guardar($event.target.value || null)"
        >

        <!-- persona -->
        <select
            v-else-if="tipo === 'persona'"
            class="form-control input-sm"
            :disabled="guardando"
            @focus="cargarCoordinadores"
            @change="guardar($event.target.value || null)"
        >
            <option value="" :selected="!persona.id"></option>
            <option
                v-if="persona.id && !coordinadores.length"
                :value="persona.id"
                selected
            >{{ persona.nombre }}</option>
            <option
                v-for="coordinador in coordinadores"
                :key="coordinador.idPersona"
                :value="coordinador.idPersona"
                :selected="coordinador.idPersona === persona.id"
            >{{ coordinador.nombre }}</option>
        </select>
    </div>
</template>

<script>
import axios from 'axios'

// caché a nivel módulo: un solo GET /ajax/coordinadores por página
let coordinadoresCache = null

export default {
    name: 'celda-seguimiento',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
        rowField: {},
        fieldDef: { type: Object, required: true },
    },
    data() {
        return {
            guardando: false,
            abierto: false,
            editando: false,
            borrador: '',
            coordinadores: [],
        }
    },
    computed: {
        meta() {
            return this.fieldDef.columnaMeta
        },
        tipo() {
            return this.meta.tipo
        },
        valor() {
            return this.rowData[this.meta.valueKey]
        },
        etiquetas() {
            return Array.isArray(this.valor) ? this.valor : []
        },
        persona() {
            return this.valor && typeof this.valor === 'object' ? this.valor : {}
        },
    },
    created() {
        this._cerrarHandler = () => { this.abierto = false }
        document.addEventListener('click', this._cerrarHandler)
    },
    beforeDestroy() {
        document.removeEventListener('click', this._cerrarHandler)
    },
    methods: {
        guardar(nuevoValor) {
            const anterior = this.valor
            this.$set(this.rowData, this.meta.valueKey, this.presentarLocal(nuevoValor))
            this.guardando = true

            axios.defaults.headers.common['X-CSRF-TOKEN'] =
                document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            const url = `/admin/ajax/listados/${this.meta.listKey}/${this.meta.contextId}` +
                `/columnas/${this.meta.id}/valores/${this.rowData.id}`

            axios.put(url, { valor: nuevoValor })
                .then(() => { this.guardando = false })
                .catch(() => {
                    this.guardando = false
                    this.$set(this.rowData, this.meta.valueKey, anterior)
                    Event.$emit('error')
                })
        },
        // Réplica local de la forma en que el backend presenta el valor,
        // para que la celda quede consistente sin refetch.
        presentarLocal(nuevoValor) {
            if (nuevoValor === null) return null
            if (this.tipo === 'etiquetas') return nuevoValor
            if (this.tipo === 'persona') {
                const coordinador = this.coordinadores.find(c => c.idPersona == nuevoValor)
                return { id: parseInt(nuevoValor, 10), nombre: coordinador ? coordinador.nombre : '' }
            }
            return nuevoValor
        },
        toggleEtiqueta(opcion) {
            const nuevas = this.etiquetas.includes(opcion)
                ? this.etiquetas.filter(e => e !== opcion)
                : this.etiquetas.concat(opcion)
            this.guardar(nuevas.length ? nuevas : null)
        },
        editarTexto() {
            this.borrador = this.valor || ''
            this.editando = true
            this.$nextTick(() => this.$refs.inputTexto && this.$refs.inputTexto.focus())
        },
        confirmarTexto() {
            if (!this.editando) return
            this.editando = false
            const texto = this.borrador.trim()
            if (texto === (this.valor || '')) return
            this.guardar(texto || null)
        },
        cargarCoordinadores() {
            if (coordinadoresCache) {
                this.coordinadores = coordinadoresCache
                return
            }
            axios.get('/ajax/coordinadores').then(({ data }) => {
                coordinadoresCache = data.data || data
                this.coordinadores = coordinadoresCache
            })
        },
    },
}
</script>

<style scoped>
.celda-seguimiento {
    min-width: 90px;
}
.celda-seguimiento .label {
    margin-right: 3px;
    display: inline-block;
}
.celda-seguimiento .dropdown-menu input[type='checkbox'] {
    margin-right: 6px;
    pointer-events: none;
}
</style>
