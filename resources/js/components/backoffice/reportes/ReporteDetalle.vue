<template>
    <div class="rd-overlay" v-if="visible" @click.self="cerrar">
        <div class="rd-modal" v-if="r">
            <div class="rd-head">
                <div>
                    <span class="label" :class="r.type === 'bug' ? 'label-danger' : 'label-info'">
                        <i class="fa" :class="r.type === 'bug' ? 'fa-bug' : 'fa-lightbulb-o'"></i>
                        {{ r.type === 'bug' ? 'Bug' : 'Sugerencia' }}
                    </span>
                    <span class="rd-id">#{{ r.id }}</span>
                    <span class="rd-fecha">{{ r.created_at }}</span>
                </div>
                <button class="rd-close" @click="cerrar"><i class="fa fa-times"></i></button>
            </div>

            <div class="rd-body">
                <!-- Descripción -->
                <p class="rd-desc">{{ r.description }}</p>

                <!-- Triage editable -->
                <div class="rd-triage">
                    <div class="rd-field">
                        <label>Estado</label>
                        <select class="form-control input-sm" v-model="estado" @change="guardar('status', estado)">
                            <option value="nuevo">Nuevo</option>
                            <option value="triage">Triage</option>
                            <option value="en_progreso">En progreso</option>
                            <option value="resuelto">Resuelto</option>
                            <option value="descartado">Descartado</option>
                        </select>
                    </div>
                    <div class="rd-field">
                        <label>Gravedad</label>
                        <select class="form-control input-sm" v-model="severity" @change="guardar('severity', severity)">
                            <option :value="null">—</option>
                            <option value="low">Baja</option>
                            <option value="medium">Media</option>
                            <option value="high">Alta</option>
                            <option value="critical">Crítica</option>
                        </select>
                    </div>
                    <div class="rd-field">
                        <label>Área</label>
                        <select class="form-control input-sm" v-model="area" @change="guardar('area', area)">
                            <option :value="null">—</option>
                            <option v-for="a in areas" :key="a.value" :value="a.value">{{ a.label }}</option>
                        </select>
                    </div>
                </div>

                <!-- Cola de trabajo (GitHub) -->
                <div class="rd-section">
                    <h5>Cola de trabajo</h5>
                    <div v-if="r.github_issue_url">
                        <i class="fa fa-github"></i>
                        <a :href="r.github_issue_url" target="_blank">{{ r.github_issue_url }}</a>
                    </div>
                    <div v-else>
                        <button class="btn btn-sm btn-default" :disabled="creandoIssue" @click="crearIssue">
                            <i class="fa fa-github"></i> {{ creandoIssue ? 'Creando…' : 'Crear issue en GitHub' }}
                        </button>
                        <span v-if="issueError" class="rd-issue-error">{{ issueError }}</span>
                    </div>
                </div>

                <!-- Captura -->
                <div class="rd-section" v-if="r.has_screenshot">
                    <h5>Captura</h5>
                    <a :href="r.screenshot_url" target="_blank">
                        <img :src="r.screenshot_url" class="rd-screenshot" alt="Captura">
                    </a>
                </div>

                <!-- Errores JS -->
                <div class="rd-section" v-if="r.console_errors && r.console_errors.length">
                    <h5>Errores JS capturados ({{ r.console_errors.length }})</h5>
                    <pre class="rd-errors">{{ prettyErrors }}</pre>
                </div>

                <!-- Quién reportó -->
                <div class="rd-section">
                    <h5>Reportó</h5>
                    <div class="rd-grid">
                        <div><span>Nombre</span> {{ r.reporter_name || '—' }}</div>
                        <div><span>Email</span> {{ r.reporter_email || '—' }}</div>
                        <div><span>Rol</span> {{ r.reporter_role || '—' }}</div>
                        <div v-if="r.assigned_name"><span>Asignado a</span> {{ r.assigned_name }}</div>
                    </div>
                </div>

                <!-- Contexto técnico -->
                <div class="rd-section">
                    <h5>Contexto técnico</h5>
                    <div class="rd-grid">
                        <div class="rd-full"><span>URL</span> <a :href="r.url" target="_blank">{{ r.url }}</a></div>
                        <div v-if="r.route_name"><span>Ruta</span> {{ r.route_name }}</div>
                        <div><span>OS</span> {{ r.os || '—' }}</div>
                        <div><span>Navegador</span> {{ r.browser || '—' }}</div>
                        <div><span>Resolución</span> {{ r.screen_resolution || '—' }}</div>
                        <div><span>Viewport</span> {{ r.viewport || '—' }}</div>
                        <div><span>Locale</span> {{ r.locale || '—' }}</div>
                        <div><span>Release</span> {{ r.release || '—' }}</div>
                        <div class="rd-full"><span>User agent</span> <code>{{ r.user_agent }}</code></div>
                        <div v-if="r.sentry_event_id"><span>Sentry</span> {{ r.sentry_event_id }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'reporte-detalle',
    data() {
        return {
            visible: false,
            r: null,
            estado: null,
            severity: null,
            area: null,
            creandoIssue: false,
            issueError: null,
            areas: [
                { value: 'inscripcion', label: 'Inscripción' },
                { value: 'pagos', label: 'Pagos' },
                { value: 'listados', label: 'Listados / tablas' },
                { value: 'actividades', label: 'Actividades' },
                { value: 'usuarios', label: 'Usuarios' },
                { value: 'comunicaciones', label: 'Comunicaciones' },
                { value: 'reportes', label: 'Reportes / export' },
                { value: 'otro', label: 'Otro' },
            ],
        };
    },
    computed: {
        prettyErrors() {
            try { return JSON.stringify(this.r.console_errors, null, 2); }
            catch (e) { return ''; }
        },
    },
    methods: {
        abrir(row) {
            this.r = row;
            this.estado = row.status;
            this.severity = row.severity || null;
            this.area = row.area || null;
            this.issueError = null;
            this.visible = true;
        },
        cerrar() { this.visible = false; this.r = null; },
        crearIssue() {
            if (this.creandoIssue) return;
            this.creandoIssue = true;
            this.issueError = null;
            axios.post('/admin/ajax/reportes/' + this.r.id + '/github')
                .then((resp) => {
                    this.r.github_issue_url = resp.data.github_issue_url;
                    this.estado = 'en_progreso';
                    Event.$emit('reporte:refrescar');
                })
                .catch((e) => {
                    this.issueError = (e.response && e.response.data && e.response.data.error)
                        ? e.response.data.error
                        : 'No se pudo crear el issue.';
                })
                .then(() => { this.creandoIssue = false; });
        },
        guardar(campo, valor) {
            const payload = {};
            payload[campo] = valor;
            axios.post('/admin/ajax/reportes/' + this.r.id, payload)
                .then(() => {
                    this.r[campo] = valor;
                    Event.$emit('reporte:refrescar');
                })
                .catch(() => { alert('No se pudo guardar el cambio.'); });
        },
    },
    mounted() {
        Event.$on('reporte:ver', this.abrir);
    },
    beforeDestroy() {
        Event.$off('reporte:ver', this.abrir);
    },
};
</script>

<style scoped>
.rd-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); z-index: 1060; display: flex; align-items: flex-start; justify-content: center; padding: 40px 16px; overflow-y: auto; }
.rd-modal { background: #fff; border-radius: 12px; width: 720px; max-width: 100%; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3); }
.rd-head { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #e2e8f0; }
.rd-id { font-weight: 700; margin-left: 8px; }
.rd-fecha { color: #94a3b8; margin-left: 8px; font-size: 13px; }
.rd-close { border: none; background: #f1f5f9; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; }
.rd-body { padding: 18px; }
.rd-desc { font-size: 15px; white-space: pre-wrap; background: #f8fafc; border-radius: 8px; padding: 12px; }
.rd-triage { display: flex; gap: 12px; margin: 16px 0; }
.rd-field { flex: 1; }
.rd-field label { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 3px; }
.rd-section { margin-top: 18px; }
.rd-section h5 { font-weight: 700; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px; }
.rd-screenshot { max-width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; }
.rd-errors { max-height: 240px; overflow: auto; font-size: 12px; background: #0f172a; color: #e2e8f0; border-radius: 8px; padding: 12px; }
.rd-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 18px; font-size: 13px; }
.rd-grid > div { word-break: break-word; }
.rd-grid .rd-full { grid-column: 1 / -1; }
.rd-grid span { display: block; font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 0.04em; }
.rd-issue-error { color: #dc2626; margin-left: 10px; font-size: 13px; }
</style>
