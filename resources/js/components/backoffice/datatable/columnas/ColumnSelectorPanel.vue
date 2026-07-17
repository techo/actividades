<template>
    <div class="column-selector btn-group" :class="{ open: abierto }">
        <button type="button" class="btn btn-default" @click.stop="toggle">
            <span class="glyphicon glyphicon-th-list"></span>
            {{ $t('backend.columns') }}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right column-selector-menu" @click.stop>
            <template v-for="grupo in grupos">
                <li class="dropdown-header" :key="'header-' + grupo.key">
                    {{ traducir(grupo.titulo) }}
                </li>
                <li v-for="campo in grupo.campos" :key="campo.key">
                    <a href="#" @click.prevent="toggleCampo(campo.key)">
                        <input type="checkbox" :checked="esVisible(campo.key)" @click.stop.prevent>
                        {{ traducir(campo.title) || '&nbsp;' }}
                        <span
                            v-if="grupo.key === 'seguimiento'"
                            class="glyphicon glyphicon-trash text-danger pull-right"
                            :title="$t('backend.delete')"
                            @click.stop.prevent="eliminarColumna(campo)"
                        ></span>
                    </a>
                </li>
                <li v-if="grupo.key === 'seguimiento'" :key="'nueva-' + grupo.key">
                    <a href="#" @click.prevent="modalNuevaColumna = true">
                        <span class="glyphicon glyphicon-plus"></span>
                        {{ $t('backend.new_column') }}
                    </a>
                </li>
                <li role="separator" class="divider" :key="'div-' + grupo.key"></li>
            </template>
        </ul>

        <nueva-columna-modal
            v-if="modalNuevaColumna"
            :list-key="listKey"
            :context-id="contextId"
            @cerrar="modalNuevaColumna = false"
            @columna-creada="onColumnaCreada"
        ></nueva-columna-modal>
    </div>
</template>

<script>
import axios from 'axios'
import NuevaColumnaModal from './NuevaColumnaModal'

export default {
    name: 'column-selector-panel',
    components: { NuevaColumnaModal },
    props: {
        listKey: { type: String, required: true },
        contextId: { type: [String, Number], required: true },
    },
    data() {
        return {
            abierto: false,
            modalNuevaColumna: false,
            fijas: [],
            grupos: [],
            visibles: [], // keys visibles, en el orden del catálogo
            cargado: false,
        }
    },
    computed: {
        baseUrl() {
            return `/admin/ajax/listados/${this.listKey}/${this.contextId}`
        },
    },
    created() {
        axios.get(`${this.baseUrl}/config`).then(({ data }) => {
            this.fijas = data.fijas
            this.grupos = data.grupos
            const conocidas = this.keysDelCatalogo()
            // preferencias del usuario, o defaults; se descartan keys que ya no existen
            const preferidas = data.preferencias !== null ? data.preferencias : data.defaults
            this.visibles = preferidas.filter(key => conocidas.includes(key))
            this.cargado = true
            this.aplicar()
        })
        this._cerrarHandler = () => { this.abierto = false }
        document.addEventListener('click', this._cerrarHandler)
    },
    beforeDestroy() {
        document.removeEventListener('click', this._cerrarHandler)
    },
    methods: {
        toggle() {
            this.abierto = !this.abierto
        },
        traducir(texto) {
            if (texto && texto.includes('.')) return this.$t(texto)
            return texto
        },
        keysDelCatalogo() {
            return this.grupos.reduce((keys, grupo) => keys.concat(grupo.campos.map(c => c.key)), [])
        },
        esVisible(key) {
            return this.visibles.includes(key)
        },
        toggleCampo(key) {
            if (this.esVisible(key)) {
                this.visibles = this.visibles.filter(k => k !== key)
            } else {
                this.visibles = this.visibles.concat(key)
            }
            this.aplicar()
            this.persistir()
        },
        aplicar() {
            // columnas en el orden del catálogo, no en el orden de activación
            const camposVisibles = this.grupos.reduce((campos, grupo) => {
                return campos.concat(grupo.campos.filter(c => this.esVisible(c.key)))
            }, [])
            Event.$emit(`columnas:aplicar:${this.listKey}`, {
                fijas: this.fijas,
                camposVisibles,
            })
        },
        persistir() {
            clearTimeout(this._persistTimer)
            this._persistTimer = setTimeout(() => {
                axios.defaults.headers.common['X-CSRF-TOKEN'] =
                    document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                axios.put(`${this.baseUrl}/preferencias`, { columnas: this.visibles })
                    .catch(() => Event.$emit('error'))
            }, 400)
        },
        onColumnaCreada(columna) {
            this.modalNuevaColumna = false
            const seguimiento = this.grupos.find(g => g.key === 'seguimiento')
            if (!seguimiento) return
            const campo = {
                key: `custom_${columna.id}`,
                name: '__component:celda-seguimiento',
                title: columna.nombre,
                columnaMeta: {
                    id: columna.id,
                    tipo: columna.tipo,
                    opciones: columna.opciones,
                    valueKey: `custom_${columna.id}`,
                    listKey: this.listKey,
                    contextId: this.contextId,
                },
            }
            seguimiento.campos.push(campo)
            this.visibles = this.visibles.concat(campo.key)
            this.aplicar()
            this.persistir()
        },
        eliminarColumna(campo) {
            if (!confirm(this.$t('backend.delete_column_confirm'))) return
            axios.defaults.headers.common['X-CSRF-TOKEN'] =
                document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            axios.delete(`${this.baseUrl}/columnas/${campo.columnaMeta.id}`)
                .then(() => {
                    const seguimiento = this.grupos.find(g => g.key === 'seguimiento')
                    seguimiento.campos = seguimiento.campos.filter(c => c.key !== campo.key)
                    this.visibles = this.visibles.filter(k => k !== campo.key)
                    this.aplicar()
                    this.persistir()
                })
                .catch(() => Event.$emit('error'))
        },
    },
}
</script>

<style scoped>
.column-selector-menu {
    max-height: 420px;
    overflow-y: auto;
    min-width: 260px;
}
.column-selector-menu input[type='checkbox'] {
    margin-right: 6px;
    pointer-events: none;
}
.column-selector-menu > li > a {
    white-space: normal;
}
</style>
