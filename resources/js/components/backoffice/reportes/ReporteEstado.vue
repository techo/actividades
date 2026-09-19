<template>
    <select class="form-control input-sm reporte-estado" :class="'estado-' + estado" v-model="estado" :disabled="guardando">
        <option value="nuevo">Nuevo</option>
        <option value="triage">Triage</option>
        <option value="en_progreso">En progreso</option>
        <option value="resuelto">Resuelto</option>
        <option value="descartado">Descartado</option>
    </select>
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
        return { estado: this.rowData.status, guardando: false };
    },
    watch: {
        estado(val, prev) {
            if (val === prev) return;
            this.guardando = true;
            axios.post('/admin/ajax/reportes/' + this.rowData.id, { status: val })
                .then(() => { Event.$emit('reporte:actualizado', { id: this.rowData.id, status: val }); })
                .catch(() => {
                    this.estado = prev; // revertir si falla
                    alert('No se pudo actualizar el estado.');
                })
                .then(() => { this.guardando = false; });
        },
        rowData() { this.estado = this.rowData.status; },
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
</style>
