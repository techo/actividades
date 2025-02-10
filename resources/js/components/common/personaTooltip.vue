<template>
  <div class="tooltip-container row mx-2">
    <div class="col-md-3" v-for="persona in personas" :key="persona.persona.idPersona">
      <!-- Imagen principal -->
      <div class="row" @click="toggleTooltip(persona.persona.idPersona)">
        <img :src="photoUrl(persona.persona)" class="imagen-hover" />
        <span class="m-1">{{ persona.persona.nombres }}</span> 
      </div>

      <!-- Tooltip que aparece solo si la persona coincide con tooltipAbierto -->
      <transition name="fade">
        <div v-if="tooltipAbierto === persona.persona.idPersona" class="custom-tooltip" ref="tooltip" @click.stop>
          <button class="close-btn" @click="cerrarTooltip">✖</button>

          <img :src="photoUrl(persona.persona)" class="tooltip-img" alt="Foto perfil" />
          <div class="tooltip-info">
            <p class="tooltip-nombre">{{ persona.persona.nombres }}</p>
            <p class="tooltip-rol">{{ persona.persona.mail }}</p>
            <p class="tooltip-rol">{{ persona.persona.estado_voluntario }}</p>

            <a v-if="persona.persona.instagram" :href="'https://instagram.com/' + persona.persona.instagram"
              target="_blank" class="tooltip-instagram">
              <i class="fab fa-instagram"></i> Instagram
            </a>

            <a v-if="isValidWhatsAppNumber(persona.persona.telefonoMovil) && persona.persona.activaWhatsapp"
              :href="'https://wa.me/' + persona.persona.telefonoMovil" target="_blank" class="tooltip-whatsapp">
              <i class="fa fa-whatsapp"></i> Whatsapp
            </a>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    personas: {
      type: Array,
      required: true
    }
  },
  data() {
    return {
      tooltipAbierto: null // Guarda el ID de la persona con tooltip abierto
    };
  },
  methods: {
    photoUrl(persona) {
      return persona.photo
        ? '/' + persona.photo
        : "/bower_components/admin-lte/dist/img/user_avatar.png";
    },
    isValidWhatsAppNumber(telefono) {
      return telefono && telefono.startsWith('+') && telefono.length >= 7;
    },
    toggleTooltip(id) {
      // Si el tooltip está abierto en este ID, lo cierra; si no, lo abre
      this.tooltipAbierto = this.tooltipAbierto === id ? null : id;
    },
    cerrarTooltip() {
      this.tooltipAbierto = null;
    },
    cerrarSiClickFuera(event) {
      // Cierra el tooltip si el clic no está dentro de ningún tooltip
      if (this.tooltipAbierto && !this.$refs.tooltip.contains(event.target)) {
        this.cerrarTooltip();
      }
    }
  },
  mounted() {
    // Escuchar clics fuera del tooltip para cerrarlo
    document.addEventListener("click", this.cerrarSiClickFuera);
  },
  beforeUnmount() {
    // Eliminar el evento cuando el componente se desmonta
    document.removeEventListener("click", this.cerrarSiClickFuera);
  }
};
</script>

<style scoped>
/* Contenedor del tooltip */
.tooltip-container {
  position: relative;
  display: flex;
  flex-wrap: wrap;
}

/* Imagen sobre la que se hace click */
.imagen-hover {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
}

/* Estilos del tooltip */
.custom-tooltip {
  position: absolute;
  bottom: 120%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 10px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 10px;
  white-space: nowrap;
  box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
  transition: opacity 0.3s ease-in-out;
  z-index: 100;
}

/* Flecha del tooltip */
.custom-tooltip::before {
  content: "";
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  border-width: 8px;
  border-style: solid;
  border-color: rgba(0, 0, 0, 0.8) transparent transparent transparent;
}

/* Botón de cierre */
.close-btn {
  position: absolute;
  top: 5px;
  right: 5px;
  background: transparent;
  border: none;
  color: white;
  font-size: 14px;
  cursor: pointer;
}

/* Foto dentro del tooltip */
.tooltip-img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
}

/* Nombre y rol */
.tooltip-nombre {
  font-weight: bold;
  margin: 0;
}

.tooltip-rol {
  font-size: 12px;
  margin: 0;
  color: #ddd;
}

/* Instagram link */
.tooltip-instagram {
  font-size: 12px;
  text-decoration: none;
  color: #ff4081;
  display: flex;
  align-items: center;
  gap: 5px;
}

/* WhatsApp link */
.tooltip-whatsapp {
  font-size: 12px;
  text-decoration: none;
  color: greenyellow;
  display: flex;
  align-items: center;
  gap: 5px;
}

/* Transición de aparición y desaparición */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease-in-out;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>