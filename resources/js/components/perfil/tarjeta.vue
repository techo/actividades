<template>
  <article class="techo-card">
    <simplert ref="confirmar"></simplert>

    <!-- Portada: foto propia si existe, luego imagen del tipo, y si no hay
         ninguna, portada generada con el color de la categoría + pictograma. -->
    <div class="tc-cover" :class="{ 'tc-cover--photo': tieneFoto }" :style="coverStyle" @click="ir_a_actividad">
      <i v-if="!tieneFoto" class="tc-ico" :class="iconoCategoria" :style="{ color: textoSobreCat }"></i>
      <span v-if="esAusente" class="tc-state tc-state--bad">{{ $t('frontend.absent') }}</span>
      <span v-else-if="actividadPasada && inscripcion.presente === 1" class="tc-state tc-state--good">
        <i class="fas fa-check"></i> {{ $t('frontend.present') }}
      </span>
    </div>

    <div class="tc-body">
      <span class="tc-kicker" :style="{ backgroundColor: colorCategoria, color: textoSobreCat }">{{ inscripcion.tipo }}</span>

      <h3 class="tc-title" @click="ir_a_actividad">{{ inscripcion.nombreActividad }}</h3>

      <div class="tc-meta">
        <span v-if="inscripcion.show_dates && inscripcion.fecha" class="tc-metarow">
          <i class="fas fa-calendar-alt"></i><b>{{ fechaLegible }}</b>
          <template v-if="inscripcion.hora">
            <span class="tc-sep">·</span><i class="fas fa-clock"></i><span>{{ inscripcion.hora }}</span>
          </template>
        </span>
        <span v-if="inscripcion.show_location && inscripcion.lugar" class="tc-metarow">
          <i class="fas fa-map-marker-alt"></i><span>{{ inscripcion.lugar }}</span>
        </span>
      </div>

      <p v-if="inscripcion.descripcion" class="tc-desc">{{ inscripcion.descripcion }}</p>

      <!-- Acciones (misma funcionalidad de siempre) -->
      <div class="tc-foot">
        <template v-if="!actividadPasada">
          <a class="tc-btn tc-btn--danger" @click.stop="desincribir(inscripcion.idActividad)">
            {{ $t('frontend.unapply') }}
          </a>
        </template>
        <template v-else>
          <a
            v-if="periodoDeEvaluacionYaComenzo && inscripcion.presente === 1"
            class="tc-btn tc-btn--info"
            @click.stop="ir_a_evaluar"
          >
            {{ $t('frontend.view_evaluations') }}
          </a>
          <span v-else-if="periodoDeEvaluacionYaComenzo && inscripcion.presente === 0" class="tc-note">
            {{ $t('frontend.absent') }}
          </span>
          <span v-else class="tc-note">
            {{ $t('frontend.evaluations_start_on') }} <b>{{ inscripcion.fechaInicioEvaluaciones }}</b>
          </span>
        </template>
      </div>
    </div>
  </article>
</template>

<script>
    export default {
        name: 'tarjeta',
        props: ['inscripcion'],
        data () {
            return {
                key: ''
            }
        },
        filters: {
            truncate: function(string, value) {
                if(!string) return '';
                return string.substr(0,value) + '...';
            },
        },
        methods: {
            ir_a_evaluar: function () {
                window.location.href = '/actividades/' + this.inscripcion.idActividad + '/evaluaciones'
            },
            ir_a_actividad: function () {
                window.location.href = '/actividades/' + this.inscripcion.idActividad
            },
            desincribir: function (idActividad) {
                let self = this;
                self.$refs.confirmar.openSimplert({
                    title: this._i18n.t('frontend.unenroll_title'),
                    message: this._i18n.t('frontend.message_1') + self.inscripcion.nombreActividad + this._i18n.t('frontend.message_2'),
                    useConfirmBtn: true,
                    isShown: true,
                    disableOverlayClick: true,
                    customClass: 'confirmar',
                    customCloseBtnText: this._i18n.t('frontend.message_1'),
                    customCloseBtnClass: 'btn btn-default',
                    customConfirmBtnText: this._i18n.t('frontend.unenroll_button'),
                    customConfirmBtnClass: 'btn btn-danger mb-1',
                    onConfirm: function() {
                        axios.delete('/ajax/usuario/inscripciones/' + idActividad).then(response => {
                            self.$parent.traer_inscripciones();
                            self.$parent.borro = true;
                            setTimeout(function(){
                                self.$parent.borro = false;
                            }, 3000)
                        })
                    }
                })
            }
        },
        computed: {
            tieneFoto() {
              return !!(this.inscripcion.imagen_tarjeta || this.inscripcion.img);
            },
            colorCategoria() {
              return this.inscripcion.tipoColor || '#009fe3';
            },
            textoSobreCat() {
              const hex = String(this.colorCategoria).replace('#', '');
              if (hex.length < 6) return '#ffffff';
              const r = parseInt(hex.substr(0, 2), 16);
              const g = parseInt(hex.substr(2, 2), 16);
              const b = parseInt(hex.substr(4, 2), 16);
              const luminancia = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
              return luminancia > 0.62 ? '#2a2300' : '#ffffff';
            },
            iconoCategoria() {
              const iconos = {
                1: 'fas fa-home',
                2: 'fas fa-graduation-cap',
                3: 'fas fa-star',
                4: 'fas fa-desktop',
                5: 'fas fa-bullhorn',
                6: 'fas fa-users',
              };
              return iconos[this.inscripcion.idCategoria] || 'fas fa-hands-helping';
            },
            coverStyle() {
              if (this.tieneFoto) {
                const url = this.inscripcion.imagen_tarjeta || this.inscripcion.img;
                return { backgroundImage: `url('${url}')` };
              }
              return { backgroundColor: this.colorCategoria };
            },
            // La fecha del recurso viene como 'd/m/Y'. La mostramos localizada.
            fechaLegible() {
              const raw = this.inscripcion.fecha;
              if (!raw) return '';
              const partes = String(raw).split('/').map(Number);
              const [d, m, y] = partes;
              if (!d || !m || !y) return raw;
              const date = new Date(y, m - 1, d);
              const locale = (document.documentElement.lang || 'es').replace('_', '-');
              try {
                const opts = { weekday: 'short', day: 'numeric', month: 'short' };
                if (y !== new Date().getFullYear()) opts.year = 'numeric';
                const s = new Intl.DateTimeFormat(locale, opts).format(date);
                return s.charAt(0).toUpperCase() + s.slice(1);
              } catch (e) {
                return raw;
              }
            },
            esAusente() {
              return this.actividadPasada
                && this.periodoDeEvaluacionYaComenzo
                && this.inscripcion.presente === 0;
            },
            actividadPasada: function () {
                let fechaFin = moment(this.inscripcion.fechaFin, "DD-MM-YYYY hh:mm");
                if (fechaFin === null || fechaFin === undefined) {
                    return false;
                }
                if (fechaFin < Date.now()) {
                    return true;
                }
            },
            periodoDeEvaluacionYaComenzo: function () {
                let fechaInicioEvaluaciones = new Date(
                    this.inscripcion.fechaInicioEvaluaciones.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3")
                ).getTime();
                let ahora = new Date().getTime();
                return (ahora > fechaInicioEvaluaciones)
            }
        }
    }
</script>

<style scoped>
.techo-card {
  width: 100%;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border: 1px solid #e4ebf1;
  border-radius: 16px;
  overflow: hidden;
  text-align: left;
  box-shadow: 0 1px 2px rgba(19, 35, 47, .05), 0 8px 24px -14px rgba(19, 35, 47, .18);
  transition: transform .18s ease, box-shadow .18s ease;
}
.techo-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 2px 6px rgba(19, 35, 47, .08), 0 22px 44px -18px rgba(19, 35, 47, .30);
}

.tc-cover {
  position: relative;
  aspect-ratio: 3 / 2;
  background-size: cover;
  background-position: center;
  display: grid;
  place-items: center;
  cursor: pointer;
}
.tc-cover:not(.tc-cover--photo)::after {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(120% 90% at 85% 12%, rgba(255, 255, 255, .28), transparent 55%),
    linear-gradient(160deg, rgba(255, 255, 255, .10), rgba(0, 0, 0, .18));
}
.tc-ico { font-size: 3.2rem; opacity: .92; z-index: 1; }
.tc-state {
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 2;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 999px;
  color: #fff;
  box-shadow: 0 2px 6px rgba(0, 0, 0, .18);
}
.tc-state--bad  { background: #e14b5a; }
.tc-state--good { background: #1ba672; }

.tc-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 14px 15px 15px;
  flex: 1;
}
.tc-kicker {
  align-self: flex-start;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .02em;
  padding: 3px 9px;
  border-radius: 999px;
}
.tc-title {
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.25;
  color: #13232f;
  margin: 0;
  cursor: pointer;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.tc-meta { display: flex; flex-direction: column; gap: 4px; }
.tc-metarow {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: .85rem;
  color: #59707f;
}
.tc-metarow i { color: #8598a6; width: 14px; text-align: center; }
.tc-metarow b { color: #13232f; font-weight: 600; }
.tc-sep { color: #b7c4cd; }
.tc-desc {
  font-size: .85rem;
  line-height: 1.5;
  color: #59707f;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.tc-foot {
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid #eef3f7;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}
.tc-btn {
  display: inline-block;
  font-size: .82rem;
  font-weight: 700;
  padding: 7px 14px;
  border-radius: 9px;
  color: #fff;
  cursor: pointer;
  transition: filter .15s ease;
}
.tc-btn:hover { filter: brightness(1.07); color: #fff; }
.tc-btn--danger { background: #e14b5a; }
.tc-btn--info { background: #009fe3; }
.tc-note {
  font-size: .8rem;
  color: #59707f;
  text-align: right;
}
.tc-note b { color: #13232f; }

/* El modal de confirmación de desinscripción (global, no scoped) */
</style>
<style>
.confirmar > div { min-width: 60%; }
.confirmar .simplert__icon { display: none; }
</style>
