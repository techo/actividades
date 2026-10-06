<template>
    <span>
    <select class="form-control input-sm reporte-estado" :class="'estado-' + estado" v-model="estado" :disabled="guardando" @change="guardar">
        <option value="nuevo">Nuevo</option>
        <option value="triage">Triage</option>
        <option value="en_progreso">En progreso</option>
        <option value="resuelto">Resuelto</option>
        <option value="descartado">Descartado</option>
    </select>
    <span v-if="rowData.respuesta_pendiente" class="reporte-respondio" title="Quien reportó contestó desde Mis reportes">
        <i class="fa fa-comment"></i> Respondió
    </span>
    </span>
</template>

<script>
import axios from 'axios';

export default {
    name: 'reporte-estado',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
    },
    data() {
        return { estado: this.rowData.status, previo: this.rowData.status, guardando: false };
    },
    methods: {
        // @change (no watch): al paginar, vuetable reutiliza el componente y cambia rowData;
        // un watch sobre `estado` disparaba un POST espurio en cada cambio de página.
        guardar() {
            const val = this.estado;
            const prev = this.previo;
            if (val === prev) return;
            this.guardando = true;
            axios.post('/admin/ajax/reportes/' + this.rowData.id, { status: val })
                .then(() => {
                    this.previo = val;
                    Event.$emit('reporte:actualizado', { id: this.rowData.id, status: val });
                })
                .catch(() => {
                    this.estado = prev; // revertir si falla
                    alert('No se pudo actualizar el estado.');
                })
                .then(() => { this.guardando = false; });
        },
    },
    watch: {
        rowData() {
            this.estado = this.rowData.status;
            this.previo = this.rowData.status;
        },
    },
};
</script>

<style scoped>
.reporte-estado { min-width: 120px; font-weight: 600; }
.estado-nuevo { color: #2563eb; }
.estado-triage { color: #b45309; }
.estado-en_progreso { color: #7c3aed; }
.estado-resuelto { color: #15803d; }
.estado-descartado { color: #6b7280; }
.reporte-respondio { display: inline-block; margin-top: 4px; font-size: 11px; font-weight: 700; color: #5b21b6; background: #ede9fe; border-radius: 10px; padding: 1px 8px; }
</style>
