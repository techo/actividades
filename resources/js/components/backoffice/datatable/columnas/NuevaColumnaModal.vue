<template>
    <div>
        <div class="modal" style="display: block;" @click.self="$emit('cerrar')">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" @click="$emit('cerrar')" aria-label="Close">
                            <span aria-hidden="true">×</span>
                        </button>
                        <h4 class="modal-title">{{ $t('backend.new_column') }}</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ $t('backend.column_name') }}</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="nombre"
                                maxlength="100"
                            >
                        </div>
                        <div class="form-group">
                            <label>{{ $t('backend.column_type') }}</label>
                            <select class="form-control" v-model="tipo">
                                <option v-for="t in tipos" :key="t" :value="t">
                                    {{ $t('backend.column_type_' + t) }}
                                </option>
                            </select>
                        </div>
                        <div class="form-group" v-if="pideOpciones">
                            <label>{{ $t('backend.column_options') }}</label>
                            <div
                                v-for="(opcion, index) in opciones"
                                :key="index"
                                class="input-group"
                                style="margin-bottom: 5px;"
                            >
                                <input type="text" class="form-control" v-model="opciones[index]" maxlength="100">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default" @click="quitarOpcion(index)">
                                        <span class="glyphicon glyphicon-minus"></span>
                                    </button>
                                </span>
                            </div>
                            <button type="button" class="btn btn-default btn-sm" @click="opciones.push('')">
                                <span class="glyphicon glyphicon-plus"></span>
                                {{ $t('backend.add') }}
                            </button>
                        </div>
                        <p class="text-danger" v-if="error">{{ error }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" @click="$emit('cerrar')">
                            {{ $t('backend.close') }}
                        </button>
                        <button type="button" class="btn btn-primary" :disabled="!puedeCrear || guardando" @click="crear">
                            {{ $t('backend.create_column') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-backdrop in"></div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    name: 'nueva-columna-modal',
    props: {
        listKey: { type: String, required: true },
        contextId: { type: [String, Number], required: true },
    },
    data() {
        return {
            nombre: '',
            tipo: 'casilla',
            tipos: ['casilla', 'estado', 'etiquetas', 'texto', 'fecha', 'persona'],
            opciones: ['', ''],
            guardando: false,
            error: null,
        }
    },
    computed: {
        pideOpciones() {
            return this.tipo === 'estado' || this.tipo === 'etiquetas'
        },
        opcionesLimpias() {
            return this.opciones.map(o => o.trim()).filter(o => o.length)
        },
        puedeCrear() {
            if (!this.nombre.trim()) return false
            if (this.pideOpciones && !this.opcionesLimpias.length) return false
            return true
        },
    },
    methods: {
        quitarOpcion(index) {
            this.opciones.splice(index, 1)
        },
        crear() {
            this.guardando = true
            this.error = null
            axios.defaults.headers.common['X-CSRF-TOKEN'] =
                document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            axios.post(`/admin/ajax/listados/${this.listKey}/${this.contextId}/columnas`, {
                nombre: this.nombre.trim(),
                tipo: this.tipo,
                opciones: this.pideOpciones ? this.opcionesLimpias : undefined,
            })
                .then(({ data }) => {
                    this.$emit('columna-creada', data.columna)
                })
                .catch(error => {
                    this.guardando = false
                    this.error = (error.response && error.response.status === 422)
                        ? Object.values(error.response.data.errors || {}).join(' ')
                        : this.$t('backend.error')
                })
        },
    },
}
</script>
