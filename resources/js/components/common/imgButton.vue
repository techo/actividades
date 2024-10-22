<template>
  <a :href="url" class="text-decoration-none">
    <div class="d-flex flex-column align-items-center p-2" style="width: 160px;"> <!-- Ajuste del ancho del contenedor -->
      <img :src="img" :alt="text" class="img-fluid mb-2" />
      <span class="text-center text-black text-truncate text-wrap" style="width: 100%;">
        {{ text }}
      </span>
    </div>
  </a>
</template>

<script>
export default {
  name: "ImgButton",
  props: {
    url: {
      type: String,
      required: true
    },
    img: {
      type: String,
      required: true
    },
    text: {
      type: String,
      required: true
    }
  }
};
</script>

<style scoped>
img {
  max-width: 80px;
  max-height: 80px;
}

span {
  display: -webkit-box;
  -webkit-line-clamp: 2; /* Limita a 2 líneas */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis; /* Añade puntos suspensivos si es necesario */
  word-break: break-word; /* Corta las palabras si es necesario */
}
</style>
