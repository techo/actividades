<template>
    <span class="roles-aplicados">
        <span v-if="!roles.length" class="text-muted">—</span>
        <a v-for="rol in roles" :key="rol.slug" href="#"
           class="label rol-chip"
           :class="rol.slug === confirmado ? 'label-success' : 'label-default'"
           :title="rol.slug === confirmado ? $t('backend.applied_roles_confirmed') : $t('backend.applied_roles_confirm')"
           @click.prevent="confirmar(rol)">
            <i v-if="rol.slug === confirmado" class="fa fa-check"></i>
            {{ rol.label }}
        </a>
    </span>
</template>

<script>
import axios from 'axios';

// Roles a los que la persona aplicó al inscribirse (Inscripcion.roles_aplicados). El que
// coincide con el rol confirmado (Inscripcion.rol, columna "Rol") se ve en verde; un click
// en otro lo confirma. Todo es computed sobre rowData: vuetable reutiliza la celda al paginar.
export default {
    name: 'roles_aplicados',
    props: {
        rowData: { type: Object, required: true },
        rowIndex: { type: Number },
    },
    data() {
        return { guardando: false };
    },
    computed: {
        confirmado() {
            return this.rowData.nombreRol || null;
        },
        roles() {
            return this.parsear(this.rowData.roles_aplicados).map((item) => {
                // Formato actual: slug ("monitor"). Legacy: { id, text }, o { text } sin id
                // (roles de texto libre): ahí el texto hace de slug.
                const slug = (item && typeof item === 'object') ? (item.id || item.text) : item;
                const texto = (item && typeof item === 'object') ? item.text : null;
                return { slug: String(slug), label: this.etiqueta(slug, texto) };
            }).filter(r => r.slug && r.slug !== 'undefined');
        },
    },
    methods: {
        parsear(valor) {
            if (!valor) return [];
            if (Array.isArray(valor)) return valor;
            try {
                const parsed = JSON.parse(valor);
                return Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                return [];
            }
        },
        etiqueta(slug, texto) {
            const clave = 'backend.roles_actividad_options.' + slug;
            if (this.$te(clave)) return this.$t(clave);
            return texto || String(slug);
        },
        confirmar(rol) {
            if (this.guardando || rol.slug === this.confirmado) return;
            this.guardando = true;
            const url = '/admin/ajax/actividades/' + this.rowData.idActividad + '/inscripciones/asignar/rol';
            axios.post(url, { rol: rol.slug, inscripciones: [this.rowData.id] })
                .then(() => {
                    // Actualiza también la columna "Rol" (lee nombreRol) sin recargar la tabla.
                    this.$set(this.rowData, 'nombreRol', rol.slug);
                })
                .catch(() => { alert(this.$t('backend.applied_roles_error')); })
                .then(() => { this.guardando = false; });
        },
    },
};
</script>

<style scoped>
.roles-aplicados { display: inline-flex; flex-wrap: wrap; gap: 4px; }
.rol-chip { cursor: pointer; font-weight: 600; }
.rol-chip.label-default:hover { background: #8fbf9f; color: #fff; }
</style>
