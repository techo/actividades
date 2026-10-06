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
                    <div class="rd-field">
                        <label>Plataforma</label>
                        <select class="form-control input-sm" v-model="platform" @change="guardar('platform', platform)">
                            <option :value="null">—</option>
                            <option value="web">Web</option>
                            <option value="app">App MiTECHO</option>
                            <option value="ambas">Web y App</option>
                        </select>
                    </div>
                </div>

                <!-- Conversación con quien reportó -->
                <div class="rd-section">
                    <h5>Conversación</h5>

                    <div v-if="!reporterEmail" class="rd-sin-email">
                        <i class="fa fa-info-circle"></i>
                        Este reporte no tiene email de contacto: podés dejar notas internas, pero no se puede avisar por mail.
                    </div>

                    <div v-if="hilo.length" class="rd-hilo">
                        <div v-for="m in hilo" :key="m.id" class="rd-msg"
                             :class="{ 'rd-msg-interna': m.is_internal, 'rd-msg-evento': m.tipo === 'resuelto', 'rd-msg-reportante': m.del_reportante }">
                            <div class="rd-msg-head">
                                <strong>{{ m.del_reportante ? 'Quien reportó' : (m.author_name || 'Equipo') }}</strong>
                                <span v-if="m.escrito_por" class="rd-msg-fecha">(escrito por {{ m.escrito_por }})</span>
                                <span class="rd-msg-fecha">{{ m.created_at }}</span>
                                <span v-if="m.is_internal" class="rd-badge rd-badge-interna">
                                    {{ m.estado_propuesto ? 'Propuesta · al aprobar: ' + etiquetaEstado(m.estado_propuesto) : 'Nota interna' }}
                                </span>
                                <span v-else-if="m.del_reportante" class="rd-badge rd-badge-reportante">Respuesta recibida</span>
                                <span v-else-if="m.notificado" class="rd-badge rd-badge-mail">
                                    <i class="fa fa-envelope"></i> Avisado por mail
                                </span>
                            </div>
                            <p v-if="m.body" class="rd-msg-body">{{ m.body }}</p>
                            <p v-if="m.tipo === 'resuelto'" class="rd-msg-body rd-msg-evento-txt">
                                Se marcó como resuelto{{ m.notificado ? ' y se avisó a quien reportó' : '' }}.
                            </p>
                            <div v-if="m.is_internal" class="rd-msg-acciones">
                                <button type="button" class="btn btn-xs btn-success" :disabled="publicando === m.id" @click.prevent="publicar(m)">
                                    <i class="fa fa-check"></i>
                                    {{ publicando === m.id ? 'Enviando…' : (reporterEmail ? 'Aprobar y enviar' : 'Aprobar') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="rd-hilo-vacio">Todavía no hay respuestas.</p>

                    <div class="rd-responder">
                        <textarea v-model="nuevoMensaje" class="form-control" rows="3"
                                  placeholder="Escribí una respuesta…"></textarea>
                        <div class="rd-responder-foot">
                            <label class="rd-visible" :class="{ 'rd-visible-off': !reporterEmail }">
                                <input type="checkbox" v-model="visibleAlReportante" :disabled="!reporterEmail">
                                Visible para quien reportó (avisar por mail)
                            </label>
                            <label class="rd-visible">
                                <input type="checkbox" v-model="comoTechita">
                                Firmar como Techita
                            </label>
                            <label v-if="!visibleAlReportante" class="rd-visible">
                                Estado al aprobar
                                <select class="form-control input-sm" v-model="estadoPropuesto" style="width:auto;">
                                    <option :value="null">Sin cambio</option>
                                    <option v-for="(label, val) in estados" :key="val" :value="val">{{ label }}</option>
                                </select>
                            </label>
                            <button type="button" class="btn btn-sm btn-primary" :disabled="enviando || !nuevoMensaje.trim()" @click.prevent="responder">
                                {{ textoBotonResponder }}
                            </button>
                        </div>
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
            platform: null,
            creandoIssue: false,
            issueError: null,
            // Conversación con quien reportó
            hilo: [],
            reporterEmail: null,
            nuevoMensaje: '',
            visibleAlReportante: true,
            comoTechita: true,
            estadoPropuesto: null,
            publicando: null,
            estados: { triage: 'Triage', en_progreso: 'En progreso', resuelto: 'Resuelto', descartado: 'Descartado' },
            enviando: false,
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
        textoBotonResponder() {
            if (this.enviando) return 'Enviando…';
            return (this.visibleAlReportante && this.reporterEmail) ? 'Responder y avisar' : 'Guardar nota';
        },
    },
    methods: {
        abrir(row) {
            this.r = row;
            this.estado = row.status;
            this.severity = row.severity || null;
            this.area = row.area || null;
            this.platform = row.platform || null;
            this.issueError = null;
            this.hilo = [];
            this.reporterEmail = null;
            this.nuevoMensaje = '';
            this.visibleAlReportante = true;
            this.comoTechita = true;
            this.estadoPropuesto = null;
            this.visible = true;
            this.cargarHilo();
        },
        cerrar() { this.visible = false; this.r = null; },
        cargarHilo() {
            if (!this.r) return;
            axios.get('/admin/ajax/reportes/' + this.r.id + '/respuestas')
                .then((resp) => {
                    this.hilo = resp.data.respuestas || [];
                    this.reporterEmail = resp.data.reporter_email || null;
                    if (!this.reporterEmail) this.visibleAlReportante = false;
                })
                .catch(() => { /* el hilo es best-effort; si falla, se puede reintentar al reabrir */ });
        },
        responder() {
            const body = this.nuevoMensaje.trim();
            if (!body || this.enviando) return;
            this.enviando = true;
            const visible = this.visibleAlReportante && !!this.reporterEmail;
            const payload = { body, visible, como_techita: this.comoTechita };
            if (!visible && this.estadoPropuesto) payload.estado_propuesto = this.estadoPropuesto;
            axios.post('/admin/ajax/reportes/' + this.r.id + '/responder', payload)
                .then((resp) => {
                    this.hilo.push(resp.data.reply);
                    this.nuevoMensaje = '';
                    this.estadoPropuesto = null;
                })
                .catch(() => { alert('No se pudo enviar la respuesta.'); })
                .then(() => { this.enviando = false; });
        },
        etiquetaEstado(val) {
            return this.estados[val] || val;
        },
        // Aprueba una respuesta propuesta: la publica, aplica su estado y manda UN mail.
        publicar(m) {
            if (this.publicando) return;
            const destino = this.reporterEmail ? ' y se le avisa por mail a quien reportó' : '';
            const estado = m.estado_propuesto ? ' El reporte pasa a "' + this.etiquetaEstado(m.estado_propuesto) + '".' : '';
            if (!confirm('Se publica esta respuesta' + destino + '.' + estado + ' ¿Continuar?')) return;
            this.publicando = m.id;
            axios.post('/admin/ajax/reportes/' + this.r.id + '/respuestas/' + m.id + '/publicar')
                .then((resp) => {
                    const i = this.hilo.findIndex(x => x.id === m.id);
                    if (i >= 0) this.$set(this.hilo, i, resp.data.reply);
                    if (resp.data.status) {
                        this.estado = resp.data.status;
                        this.r.status = resp.data.status;
                    }
                    Event.$emit('reporte:refrescar');
                })
                .catch((e) => {
                    alert((e.response && e.response.data && e.response.data.error) || 'No se pudo publicar la respuesta.');
                })
                .then(() => { this.publicando = null; });
        },
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
                .then((resp) => {
                    this.r[campo] = valor;
                    Event.$emit('reporte:refrescar');
                    // Al resolver se genera un aviso en el hilo; lo recargamos para mostrarlo.
                    if (campo === 'status') {
                        this.cargarHilo();
                        if (resp.data && resp.data.notificado) {
                            alert('Se marcó como resuelto y se avisó por mail a quien reportó.');
                        }
                    }
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

/* Conversación */
.rd-sin-email { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; border-radius: 8px; padding: 10px 12px; font-size: 13px; margin-bottom: 12px; }
.rd-hilo { display: flex; flex-direction: column; gap: 10px; margin-bottom: 14px; }
.rd-msg { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; background: #fff; }
.rd-msg-interna { background: #fffbeb; border-color: #fde68a; }
.rd-msg-evento { background: #f0fdf4; border-color: #bbf7d0; }
.rd-msg-head { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b; margin-bottom: 4px; }
.rd-msg-head strong { color: #334155; }
.rd-msg-fecha { color: #94a3b8; }
.rd-badge { font-size: 11px; padding: 1px 7px; border-radius: 10px; font-weight: 600; margin-left: auto; }
.rd-badge-interna { background: #fde68a; color: #92400e; }
.rd-badge-mail { background: #dbeafe; color: #1e40af; }
.rd-badge-reportante { background: #ede9fe; color: #5b21b6; }
.rd-msg-reportante { background: #f5f3ff; border-color: #ddd6fe; }
.rd-msg-acciones { margin-top: 8px; text-align: right; }
.rd-msg-body { margin: 0; font-size: 14px; line-height: 1.5; color: #2b2f36; white-space: pre-wrap; }
.rd-msg-evento-txt { color: #166534; font-weight: 600; }
.rd-hilo-vacio { color: #94a3b8; font-size: 13px; font-style: italic; margin-bottom: 14px; }
.rd-responder textarea { resize: vertical; }
.rd-responder-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 8px; flex-wrap: wrap; }
.rd-visible { font-size: 13px; color: #475569; font-weight: 500; margin: 0; display: flex; align-items: center; gap: 6px; cursor: pointer; }
.rd-visible-off { color: #94a3b8; cursor: not-allowed; }
</style>
