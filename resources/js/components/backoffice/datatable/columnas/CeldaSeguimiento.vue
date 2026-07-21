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

        <!-- persona: buscador con typeahead sobre personas del país -->
        <v-select
            v-else-if="tipo === 'persona'"
            class="celda-seguimiento__persona"
            :options="personas"
            :value="valorSelect"
            label="nombre"
            :filterable="false"
            :disabled="guardando"
            @search="onSearch"
            @input="onSelectPersona"
        >
            <template slot="no-options">{{ $t('backend.enter_name_lastname_or_dni') }}</template>
        </v-select>
    </div>
</template>

<script>
import axios from 'axios'

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
            personas: [],       // resultados del buscador (persona)
            _buscarTimer: null,
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
        // Opción seleccionada del v-select (label="nombre").
        valorSelect() {
            return this.persona.id ? { idPersona: this.persona.id, nombre: this.persona.nombre } : null
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
                return { id: parseInt(nuevoValor, 10), nombre: this._personaNombre || '' }
            }
            return nuevoValor
        },
        // Buscador de personas del país: mismo patrón que el modal de inscribir.
        // El scope por país lo garantiza CoordinadoresSearch en el backend.
        onSearch(texto, loading) {
            if (!texto || texto.length <= 3) return
            loading(true)
            clearTimeout(this._buscarTimer)
            this._buscarTimer = setTimeout(() => {
                axios.get('/ajax/coordinadores?coordinador=' + encodeURIComponent(texto))
                    .then(({ data }) => {
                        this.personas = data.data || data
                        loading(false)
                    })
                    .catch(() => loading(false))
            }, 400)
        },
        onSelectPersona(opcion) {
            this._personaNombre = opcion ? opcion.nombre : ''
            this.guardar(opcion ? opcion.idPersona : null)
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
.celda-seguimiento__persona {
    min-width: 200px;
}
</style>
