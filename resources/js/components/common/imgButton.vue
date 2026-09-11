<template>
  <a :href="buildImageUrl()" class="text-decoration-none filtro-tipo">
    <div class="d-flex flex-column align-items-center p-1 filtro-tipo-item">
      <img :src="img" :alt="text" class="img-fluid mb-2" />
      <span class="text-center text-truncate text-wrap" style="width: 100%; line-height: 1.2;">
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
  },
  methods: {
    buildImageUrl() {
      return window.location.pathname + '/' + this.url;
    }
  }
};
</script>

<style scoped>
.filtro-tipo-item {
  width: 100px;
}

.filtro-tipo-item span {
  font-size: 12px;
}

img {
  max-width: 56px;
  max-height: 56px;
}

@media (min-width: 576px) {
  .filtro-tipo-item {
    width: 116px;
  }

  .filtro-tipo-item span {
    font-size: 14px;
  }

  img {
    max-width: 72px;
    max-height: 72px;
  }
}

.filtro-tipo:hover {
    filter: brightness(1.2);
}

span {
  display: -webkit-box;
  -webkit-line-clamp: 2; /* Limita a 2 líneas */
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis; /* Añade puntos suspensivos si es necesario */
  word-break: break-word; /* Corta las palabras si es necesario */
  color: black;
}
</style>
