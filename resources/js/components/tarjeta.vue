<template>
  <article class="techo-card" @click="ir_a_actividad">
    <!-- Portada: foto propia de la actividad si existe; si no, la imagen del
         tipo; y si tampoco hay, portada generada con el color de la categoría
         + un pictograma. -->
    <div class="tc-cover" :class="{ 'tc-cover--photo': tieneFoto }" :style="coverStyle">
      <i v-if="!tieneFoto" class="tc-ico" :class="iconoCategoria" :style="{ color: textoSobreCat }"></i>

      <span
        v-if="actividad.estadoInscripcion"
        class="tc-state"
        :class="claseEstadoInscripcion"
      >{{ $t('frontend.' + actividad.estadoInscripcion) }}</span>
      <span v-else-if="cuposLlenos" class="tc-state tc-state--bad">{{ $t('frontend.activity_full') }}</span>
      <span v-else-if="fechaLimitePagoVencida" class="tc-state tc-state--bad">{{ $t('frontend.confirmation_date_is_closed') }}</span>
      <span v-else-if="pocosCupos" class="tc-state tc-state--warn">{{ $t('frontend.limit_about_to_be_reached') }}</span>
    </div>

    <div class="tc-body">
      <span class="tc-kicker" :style="{ backgroundColor: colorCategoria, color: textoSobreCat }">{{ actividad.tipo.nombre }}</span>

      <h3 class="tc-title">{{ actividad.nombreActividad }}</h3>

      <div class="tc-meta">
        <span v-if="actividad.show_dates && (actividad.fechaInicio || actividad.fecha)" class="tc-metarow">
          <i class="fas fa-calendar-alt"></i><b>{{ fechaLegible }}</b>
          <template v-if="actividad.hora">
            <span class="tc-sep">·</span><i class="fas fa-clock"></i><span>{{ actividad.hora }}</span>
          </template>
        </span>
        <span v-if="actividad.show_location && actividad.ubicacion" class="tc-metarow">
          <i class="fas fa-map-marker-alt"></i><span>{{ actividad.ubicacion }}</span>
        </span>
      </div>

      <p v-if="actividad.descripcion" class="tc-desc">{{ actividad.descripcion }}</p>

      <div class="tc-foot">
        <span class="tc-more">{{ $t('frontend.card_more') }} <i class="fas fa-arrow-right"></i></span>
        <span class="tc-price" :class="{ 'tc-price--free': !esPago }">
          {{ esPago ? $t('frontend.card_paid') : $t('frontend.card_free') }}
        </span>
      </div>
    </div>
  </article>
</template>

<script>
    export default {
        name: 'tarjeta',
        props: ['actividad'],
        computed: {
          // Prioridad de la portada: foto propia de la actividad, luego la
          // imagen del tipo, y si no hay ninguna se cae a la portada generada.
          fotoUrl() {
            return this.actividad.imagen_tarjeta
              || (this.actividad.tipo && this.actividad.tipo.imagen)
              || null;
          },
          tieneFoto() {
            return !!this.fotoUrl;
          },
          colorCategoria() {
            // El color viene de la categoría (TipoResource: categoria->color).
            return (this.actividad.tipo && this.actividad.tipo.color) || '#009fe3';
          },
          // Blanco o casi-negro según el brillo del color de categoría, para que
          // el texto del pill y el pictograma siempre tengan contraste (ej: el
          // amarillo de "Online" necesita texto oscuro).
          textoSobreCat() {
            const hex = this.colorCategoria.replace('#', '');
            if (hex.length < 6) return '#ffffff';
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            const luminancia = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
            return luminancia > 0.62 ? '#2a2300' : '#ffffff';
          },
          iconoCategoria() {
            const iconos = {
              1: 'fas fa-home',            // to_act
              2: 'fas fa-graduation-cap',  // to_reflect_and_learn
              3: 'fas fa-star',            // especial_events
              4: 'fas fa-desktop',         // online_events
              5: 'fas fa-bullhorn',        // campañas
              6: 'fas fa-users',           // application
            };
            const idCat = this.actividad.tipo && this.actividad.tipo.idCategoria;
            return iconos[idCat] || 'fas fa-hands-helping';
          },
          coverStyle() {
            if (this.tieneFoto) {
              return { backgroundImage: `url('${this.fotoUrl}')` };
            }
            return { backgroundColor: this.colorCategoria };
          },
          esPago() {
            return this.actividad.pago == 1;
          },
          claseEstadoInscripcion() {
            const map = {
              confirmation_date_is_closed: 'tc-state--bad',
              confirm_by_paying: 'tc-state--info',
              waiting_for_confirmation: 'tc-state--warn',
              confirmed: 'tc-state--good',
            };
            return map[this.actividad.estadoInscripcion] || 'tc-state--info';
          },
          // Fecha localizada (ej: "Sáb 12 abr"), con año solo si no es el actual.
          // Usa Intl con el locale del documento (multi-país) sin depender de
          // moment, que en este proyecto no tiene locale configurado.
          fechaLegible() {
            const raw = this.actividad.fechaInicio; // 'd-m-Y'
            if (!raw) return this.actividad.fecha || '';
            const partes = raw.split('-').map(Number);
            const [d, m, y] = partes;
            if (!d || !m || !y) return this.actividad.fecha || '';
            const date = new Date(y, m - 1, d);
            const locale = (document.documentElement.lang || 'es').replace('_', '-');
            try {
              const opts = { weekday: 'short', day: 'numeric', month: 'short' };
              if (y !== new Date().getFullYear()) opts.year = 'numeric';
              const s = new Intl.DateTimeFormat(locale, opts).format(date);
              return s.charAt(0).toUpperCase() + s.slice(1);
            } catch (e) {
              return this.actividad.fecha || '';
            }
          },
          pocosCupos() {
              const umbral = 0.9;
              if (this.actividad.limiteInscripciones === 0) {
                  return false;
              }
              const porcentajeActual = this.actividad.cantInscriptos / this.actividad.limiteInscripciones;
              return porcentajeActual >= umbral && !this.cuposLlenos;
          },
          cuposLlenos() {
              return this.actividad.cuposRestantes <= 0 && this.actividad.limiteInscripciones !== 0;
          },
          fechaLimitePagoVencida() {
            const hoy = moment(moment().format("MM-DD-YYYY"), "MM-DD-YYYY");
            const fecha_limite = moment(this.actividad.fechaLimitePago, "DD-MM-YYYY");
            return this.actividad.pago == 1 && this.actividad.fechaLimitePago != '' && fecha_limite < hoy;
          },
        },
        methods: {
            ir_a_actividad() {
                window.location.href = '/actividades/' + this.actividad.idActividad;
            },
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
  cursor: pointer;
  text-align: left;
  box-shadow: 0 1px 2px rgba(19, 35, 47, .05), 0 8px 24px -14px rgba(19, 35, 47, .18);
  transition: transform .18s ease, box-shadow .18s ease;
}
.techo-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 2px 6px rgba(19, 35, 47, .08), 0 22px 44px -18px rgba(19, 35, 47, .30);
}

/* -------- Portada -------- */
.tc-cover {
  position: relative;
  aspect-ratio: 3 / 2;
  background-size: cover;
  background-position: center;
  display: grid;
  place-items: center;
}
/* velo sutil sobre las portadas generadas para dar profundidad y contraste */
.tc-cover:not(.tc-cover--photo)::after {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(120% 90% at 85% 12%, rgba(255, 255, 255, .28), transparent 55%),
    linear-gradient(160deg, rgba(255, 255, 255, .10), rgba(0, 0, 0, .18));
}
.tc-ico {
  font-size: 3.2rem;
  opacity: .92;
  z-index: 1;
}
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
.tc-state--warn { background: #e08a1e; }
.tc-state--good { background: #1ba672; }
.tc-state--info { background: #009fe3; }

/* -------- Cuerpo -------- */
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
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.tc-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
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
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #eef3f7;
}
.tc-more {
  font-size: .82rem;
  font-weight: 700;
  color: #0077aa;
}
.tc-more i { margin-left: 3px; font-size: .75rem; }
.tc-price {
  font-size: .8rem;
  font-weight: 600;
  color: #59707f;
}
.tc-price--free { color: #1ba672; }
</style>
