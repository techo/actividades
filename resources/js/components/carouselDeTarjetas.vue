<template>
  <div class="tarjetas-agrupadas">
    <div class="contenedor-titulo-controles">
      <h4 class="mt-2 mb-0">{{ title }}</h4>
      <div class="arrows" v-show="hayOverflow">
        <button class="flecha" :disabled="atStart" @click="scroll(-1)" aria-label="Anterior">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button class="flecha" :disabled="atEnd" @click="scroll(1)" aria-label="Siguiente">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <div ref="track" class="carousel" @scroll.passive="updateState">
      <tarjeta
        class="tarjeta"
        v-for="act in actividades"
        :key="act.idActividad"
        :actividad="act"
      />
    </div>
  </div>
</template>

<script>
import Tarjeta from './tarjeta';

export default {
  components: { Tarjeta },
  props: {
    actividades: { type: Array, required: true },
    title: { type: String, required: true },
  },
  data() {
    return {
      atStart: true,
      atEnd: false,
      hayOverflow: false,
      drag: { active: false, x0: 0, l0: 0, moved: false },
    };
  },
  mounted() {
    this.$nextTick(this.updateState);
    window.addEventListener('resize', this.updateState);
    this.wireDrag();
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.updateState);
  },
  watch: {
    actividades() {
      this.$nextTick(this.updateState);
    },
  },
  methods: {
    updateState() {
      const el = this.$refs.track;
      if (!el) return;
      const max = el.scrollWidth - el.clientWidth;
      this.hayOverflow = max > 2;
      this.atStart = el.scrollLeft <= 2;
      this.atEnd = el.scrollLeft >= max - 2;
    },
    scroll(dir) {
      const el = this.$refs.track;
      if (!el) return;
      el.scrollBy({ left: dir * el.clientWidth * 0.85, behavior: 'smooth' });
    },
    // Arrastre con mouse (el swipe táctil ya funciona nativo con overflow-x:auto).
    wireDrag() {
      const el = this.$refs.track;
      if (!el) return;
      el.addEventListener('mousedown', (e) => {
        this.drag.active = true;
        this.drag.moved = false;
        this.drag.x0 = e.pageX;
        this.drag.l0 = el.scrollLeft;
      });
      el.addEventListener('mousemove', (e) => {
        if (!this.drag.active) return;
        const dx = e.pageX - this.drag.x0;
        if (Math.abs(dx) > 4) this.drag.moved = true;
        el.scrollLeft = this.drag.l0 - dx;
      });
      window.addEventListener('mouseup', () => { this.drag.active = false; });
      // Si el usuario arrastró, cancelamos el click para no navegar a la actividad.
      el.addEventListener('click', (e) => {
        if (this.drag.moved) {
          e.stopPropagation();
          e.preventDefault();
        }
      }, true);
    },
  },
};
</script>

<style scoped>
.tarjetas-agrupadas {
  margin-bottom: 34px;
}

.contenedor-titulo-controles {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.contenedor-titulo-controles h4 {
  font-weight: 700;
}

.arrows {
  display: flex;
  gap: 8px;
}
.flecha {
  width: 34px;
  height: 34px;
  border-radius: 9px;
  border: 1px solid #dde6ee;
  background: #fff;
  color: #13232f;
  cursor: pointer;
  display: inline-grid;
  place-items: center;
  transition: border-color .15s ease, color .15s ease;
}
.flecha:hover:not(:disabled) {
  border-color: #009fe3;
  color: #009fe3;
}
.flecha:disabled {
  opacity: .35;
  cursor: default;
}

.carousel {
  display: flex;
  gap: 16px;
  padding: 6px 2px 14px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  scroll-behavior: smooth;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  cursor: grab;
}
.carousel::-webkit-scrollbar { display: none; }
.carousel:active { cursor: grabbing; }

/* Cada tarjeta es un "slot" de ancho fijo con snap. En mobile asoma la
   siguiente (85%) para que se note que el carrusel desliza. */
.carousel .tarjeta {
  flex: 0 0 auto;
  scroll-snap-align: start;
  min-width: 280px;
  width: 280px;
}
@media (min-width: 1200px) {
  .carousel .tarjeta { min-width: calc((100% - 48px) / 4); width: calc((100% - 48px) / 4); }
}
@media (min-width: 992px) and (max-width: 1199px) {
  .carousel .tarjeta { min-width: calc((100% - 32px) / 3); width: calc((100% - 32px) / 3); }
}
@media (min-width: 768px) and (max-width: 991px) {
  .carousel .tarjeta { min-width: calc((100% - 16px) / 2); width: calc((100% - 16px) / 2); }
}
@media (max-width: 767px) {
  .carousel .tarjeta { min-width: 85%; width: 85%; }
}
</style>
