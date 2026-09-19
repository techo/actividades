<template>
    <div class="bug-reporter">
        <!-- Botón flotante -->
        <button v-if="!open" class="br-fab" @click="abrir" title="Reportar un problema">
            <i class="fa fa-bug"></i>
            <span class="br-fab-label">Reportar</span>
        </button>

        <!-- Panel -->
        <div v-if="open" class="br-panel">
            <div class="br-header">
                <div class="br-header-title">
                    <i class="fa fa-flag"></i>
                    <div>
                        <strong>Reportar un problema</strong>
                        <div class="br-subtitle">Ayúdanos a mejorar el sistema</div>
                    </div>
                </div>
                <button class="br-close" @click="cerrar" aria-label="Cerrar"><i class="fa fa-times"></i></button>
            </div>

            <div class="br-body" v-if="!enviado">
                <!-- Tipo -->
                <div class="br-tipos">
                    <button
                        class="br-tipo"
                        :class="{ 'br-tipo--bug': true, 'active': tipo === 'bug' }"
                        @click="tipo = 'bug'">
                        <i class="fa fa-bug"></i> Error / Bug
                    </button>
                    <button
                        class="br-tipo"
                        :class="{ 'br-tipo--sug': true, 'active': tipo === 'suggestion' }"
                        @click="tipo = 'suggestion'">
                        <i class="fa fa-lightbulb-o"></i> Sugerencia
                    </button>
                </div>

                <!-- Descripción -->
                <label class="br-label">
                    {{ tipo === 'bug' ? '¿Qué está pasando?' : '¿Qué propones?' }}
                    <span class="br-req">*</span>
                </label>
                <textarea
                    v-model="descripcion"
                    class="br-textarea"
                    rows="4"
                    :placeholder="tipo === 'bug' ? 'Describe el error paso a paso…' : 'Describe tu sugerencia…'"></textarea>

                <!-- Clasificación (Fase 2) -->
                <div class="br-clasif" v-if="tipo === 'bug'">
                    <div class="br-field">
                        <label class="br-label-sm">Gravedad</label>
                        <select v-model="severity" class="form-control input-sm">
                            <option :value="null">Sin especificar</option>
                            <option value="low">Baja</option>
                            <option value="medium">Media</option>
                            <option value="high">Alta</option>
                            <option value="critical">Crítica</option>
                        </select>
                    </div>
                    <div class="br-field">
                        <label class="br-label-sm">Área</label>
                        <select v-model="area" class="form-control input-sm">
                            <option :value="null">Sin especificar</option>
                            <option v-for="a in areas" :key="a.value" :value="a.value">{{ a.label }}</option>
                        </select>
                    </div>
                </div>

                <!-- Captura -->
                <div class="br-captura-head">
                    <span class="br-label">Captura de pantalla</span>
                    <div class="br-captura-actions">
                        <button class="br-btn-link" @click="tomarCaptura" :disabled="capturando">
                            <i class="fa fa-camera"></i> {{ capturando ? 'Capturando…' : 'Tomar captura' }}
                        </button>
                        <button class="br-btn-link" @click="$refs.file.click()">
                            <i class="fa fa-paperclip"></i> Adjuntar
                        </button>
                        <input ref="file" type="file" accept="image/png,image/jpeg" class="br-hidden" @change="onArchivo">
                    </div>
                </div>

                <div v-if="capturaUrl" class="br-preview">
                    <img :src="capturaUrl" alt="Captura">
                    <button class="br-preview-remove" @click="quitarCaptura" title="Quitar"><i class="fa fa-times"></i></button>
                </div>
                <div v-else class="br-captura-hint">
                    <i class="fa fa-image"></i>
                    Es muy recomendable adjuntar una captura. Podés tomarla de la pantalla, adjuntar un archivo o pegar
                    una imagen con <kbd>Ctrl</kbd>+<kbd>V</kbd>.
                </div>

                <!-- Datos automáticos -->
                <div class="br-meta">
                    <div class="br-meta-title"><i class="fa fa-desktop"></i> Datos que se enviarán</div>
                    <div class="br-meta-row"><span>OS:</span> {{ meta.os }}</div>
                    <div class="br-meta-row"><span>Navegador:</span> {{ meta.browser }}</div>
                    <div class="br-meta-row"><span>Resolución:</span> {{ meta.screen_resolution }}</div>
                    <div class="br-meta-row br-meta-url"><span>URL:</span> {{ meta.url }}</div>
                    <div class="br-meta-row" v-if="meta.console_errors.length">
                        <span>Errores JS:</span> {{ meta.console_errors.length }} capturado(s)
                    </div>
                </div>

                <div v-if="error" class="br-error">{{ error }}</div>

                <button class="br-submit" :disabled="!puedeEnviar || enviando" @click="enviar">
                    <i class="fa fa-paper-plane"></i> {{ enviando ? 'Enviando…' : 'Enviar reporte' }}
                </button>
            </div>

            <!-- Éxito -->
            <div class="br-body br-exito" v-else>
                <i class="fa fa-check-circle"></i>
                <p><strong>¡Gracias!</strong></p>
                <p>Tu reporte fue enviado. El equipo lo va a revisar.</p>
                <button class="br-btn-link" @click="reset">Enviar otro</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BugReporter',
    data() {
        return {
            open: false,
            tipo: 'bug',
            descripcion: '',
            severity: null,
            area: null,
            capturando: false,
            capturaBlob: null,
            capturaUrl: null,
            enviando: false,
            enviado: false,
            error: null,
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
            meta: {
                os: '',
                browser: '',
                screen_resolution: '',
                viewport: '',
                url: '',
                locale: '',
                user_agent: '',
                console_errors: [],
            },
        };
    },
    computed: {
        puedeEnviar() {
            return this.descripcion.trim().length > 0;
        },
    },
    methods: {
        abrir() {
            this.recolectarMeta();
            this.open = true;
        },
        cerrar() {
            this.open = false;
        },
        recolectarMeta() {
            const ua = navigator.userAgent;
            this.meta = {
                os: this.detectarOS(ua),
                browser: this.detectarBrowser(ua),
                screen_resolution: (window.screen.width || 0) + 'x' + (window.screen.height || 0),
                viewport: (window.innerWidth || 0) + 'x' + (window.innerHeight || 0),
                url: window.location.href,
                locale: document.documentElement.lang || navigator.language || '',
                user_agent: ua,
                console_errors: (window.__issueErrors || []).slice(),
            };
        },
        detectarOS(ua) {
            if (/Windows NT 10/.test(ua)) return 'Windows 10/11';
            if (/Windows/.test(ua)) return 'Windows';
            if (/Mac OS X/.test(ua)) return 'macOS';
            if (/Android/.test(ua)) return 'Android';
            if (/iPhone|iPad|iPod/.test(ua)) return 'iOS';
            if (/Linux/.test(ua)) return 'Linux';
            return 'Desconocido';
        },
        detectarBrowser(ua) {
            if (/Edg\//.test(ua)) return 'Edge';
            if (/OPR\//.test(ua)) return 'Opera';
            if (/Chrome\//.test(ua)) return 'Chrome';
            if (/Firefox\//.test(ua)) return 'Firefox';
            if (/Safari\//.test(ua)) return 'Safari';
            return 'Desconocido';
        },

        // --- Captura de pantalla ---
        async tomarCaptura() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
                this.error = 'Tu navegador no permite capturar la pantalla. Usá "Adjuntar" o pegá una imagen.';
                return;
            }
            this.error = null;
            this.capturando = true;
            let stream = null;
            try {
                stream = await navigator.mediaDevices.getDisplayMedia({ video: { displaySurface: 'browser' } });
                const track = stream.getVideoTracks()[0];
                const video = document.createElement('video');
                video.srcObject = stream;
                await video.play();
                // Un pequeño respiro para que el primer frame esté listo.
                await new Promise((r) => setTimeout(r, 200));
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                video.pause();
                await new Promise((resolve) => canvas.toBlob((b) => { this.setCaptura(b); resolve(); }, 'image/png'));
            } catch (e) {
                // El usuario canceló el diálogo de compartir: no es un error a mostrar.
                if (e && e.name !== 'NotAllowedError' && e.name !== 'AbortError') {
                    this.error = 'No se pudo tomar la captura. Probá con "Adjuntar".';
                }
            } finally {
                if (stream) stream.getTracks().forEach((t) => t.stop());
                this.capturando = false;
            }
        },
        onArchivo(e) {
            const f = e.target.files && e.target.files[0];
            if (f) this.setCaptura(f);
            e.target.value = '';
        },
        onPaste(e) {
            if (!this.open || this.enviado) return;
            const items = (e.clipboardData || {}).items || [];
            for (let i = 0; i < items.length; i++) {
                if (items[i].type && items[i].type.indexOf('image') === 0) {
                    this.setCaptura(items[i].getAsFile());
                    e.preventDefault();
                    break;
                }
            }
        },
        setCaptura(blob) {
            if (!blob) return;
            if (this.capturaUrl) URL.revokeObjectURL(this.capturaUrl);
            this.capturaBlob = blob;
            this.capturaUrl = URL.createObjectURL(blob);
        },
        quitarCaptura() {
            if (this.capturaUrl) URL.revokeObjectURL(this.capturaUrl);
            this.capturaBlob = null;
            this.capturaUrl = null;
        },

        // --- Envío ---
        async enviar() {
            if (!this.puedeEnviar || this.enviando) return;
            this.enviando = true;
            this.error = null;
            try {
                const payload = Object.assign({}, this.meta, {
                    type: this.tipo,
                    description: this.descripcion.trim(),
                    severity: this.tipo === 'bug' ? this.severity : null,
                    area: this.tipo === 'bug' ? this.area : null,
                });
                const { data } = await window.axios.post('/admin/ajax/reportes', payload);
                if (this.capturaBlob && data && data.id) {
                    const fd = new FormData();
                    const nombre = 'captura.' + (this.capturaBlob.type === 'image/jpeg' ? 'jpg' : 'png');
                    fd.append('captura', this.capturaBlob, nombre);
                    try {
                        await window.axios.post('/admin/ajax/reportes/' + data.id + '/captura', fd);
                    } catch (e) {
                        // El reporte ya se guardó; la captura es best-effort.
                    }
                }
                this.enviado = true;
            } catch (e) {
                this.error = 'No se pudo enviar el reporte. Intentá de nuevo.';
            } finally {
                this.enviando = false;
            }
        },
        reset() {
            this.quitarCaptura();
            this.descripcion = '';
            this.severity = null;
            this.area = null;
            this.tipo = 'bug';
            this.enviado = false;
            this.error = null;
            this.recolectarMeta();
        },
    },
    mounted() {
        this._onPaste = this.onPaste.bind(this);
        window.addEventListener('paste', this._onPaste);
    },
    beforeDestroy() {
        window.removeEventListener('paste', this._onPaste);
        if (this.capturaUrl) URL.revokeObjectURL(this.capturaUrl);
    },
};
</script>

<style scoped>
.br-fab {
    position: fixed;
    bottom: 22px;
    right: 22px;
    z-index: 1050;
    display: flex;
    align-items: center;
    gap: 8px;
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 999px;
    padding: 11px 18px;
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.12s ease, box-shadow 0.12s ease;
}
.br-fab:hover { transform: translateY(-1px); box-shadow: 0 8px 26px rgba(37, 99, 235, 0.45); }
.br-fab-label { font-size: 14px; }

.br-panel {
    position: fixed;
    bottom: 22px;
    right: 22px;
    z-index: 1051;
    width: 380px;
    max-width: calc(100vw - 24px);
    max-height: calc(100vh - 40px);
    overflow-y: auto;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.3);
    font-size: 14px;
    color: #1e293b;
}

.br-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    padding: 16px 18px;
    border-radius: 14px 14px 0 0;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}
.br-header-title { display: flex; gap: 10px; align-items: center; }
.br-header-title i { font-size: 18px; margin-top: 2px; }
.br-subtitle { font-size: 12px; opacity: 0.85; font-weight: 400; }
.br-close { background: rgba(255, 255, 255, 0.18); border: none; color: #fff; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; }

.br-body { padding: 16px 18px 20px; }

.br-tipos { display: flex; gap: 10px; margin-bottom: 14px; }
.br-tipo {
    flex: 1;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    border-radius: 10px;
    padding: 10px;
    cursor: pointer;
    font-weight: 600;
    color: #475569;
    transition: all 0.12s ease;
}
.br-tipo--bug.active { border-color: #ef4444; color: #ef4444; background: #fef2f2; }
.br-tipo--sug.active { border-color: #2563eb; color: #2563eb; background: #eff6ff; }

.br-label { display: block; font-weight: 700; margin-bottom: 6px; }
.br-label-sm { display: block; font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 3px; }
.br-req { color: #ef4444; }
.br-textarea { width: 100%; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px; resize: vertical; }
.br-textarea:focus { border-color: #2563eb; outline: none; }

.br-clasif { display: flex; gap: 10px; margin-top: 12px; }
.br-field { flex: 1; }

.br-captura-head { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; margin-bottom: 8px; }
.br-captura-actions { display: flex; gap: 6px; }
.br-btn-link { background: #eff6ff; color: #2563eb; border: none; border-radius: 8px; padding: 6px 10px; font-weight: 600; cursor: pointer; font-size: 13px; }
.br-btn-link:disabled { opacity: 0.6; cursor: default; }
.br-hidden { display: none; }

.br-captura-hint { border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 12px; color: #64748b; font-size: 13px; line-height: 1.5; }
.br-captura-hint kbd { background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 4px; font-size: 11px; }

.br-preview { position: relative; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
.br-preview img { display: block; width: 100%; }
.br-preview-remove { position: absolute; top: 6px; right: 6px; background: rgba(15, 23, 42, 0.7); color: #fff; border: none; width: 26px; height: 26px; border-radius: 50%; cursor: pointer; }

.br-meta { background: #f8fafc; border-radius: 10px; padding: 12px; margin-top: 16px; font-family: monospace; font-size: 12px; color: #475569; }
.br-meta-title { font-family: inherit; font-weight: 700; color: #334155; margin-bottom: 6px; }
.br-meta-row span { color: #94a3b8; }
.br-meta-url { word-break: break-all; }

.br-error { color: #dc2626; background: #fef2f2; border-radius: 8px; padding: 8px 10px; margin-top: 12px; font-size: 13px; }

.br-submit {
    width: 100%;
    margin-top: 16px;
    background: #2563eb;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 12px;
    font-weight: 700;
    cursor: pointer;
}
.br-submit:disabled { background: #93c5fd; cursor: default; }

.br-exito { text-align: center; padding: 32px 18px; }
.br-exito i { font-size: 42px; color: #22c55e; }
.br-exito p { margin: 6px 0; }
</style>
