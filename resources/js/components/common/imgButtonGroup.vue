<template>
  <div class="container">
    <h4 class="text-center fw-bold pb-2">
        {{ $t('frontend.sumate.titulo') }}
        <span class="techo-blue">{{ $t('frontend.sumate.transforma') }}</span>!
    </h4>
    <div class="categorias-tipos d-flex flex-wrap justify-content-center w-100 mb-4">
      <imgButton
        v-for="(button, index) in buttons"
        :key="index"
        :url="button.url"
        :img="button.img"
        :text="button.text"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'ButtonGroup',
  data() {
    return {
      buttons: [
        {
          url: 'filtro?tipo[]=11&tipo[]=27&tipo[]=65&tipo[]=72&tipo[]=73&tipo[]=80&tipo[]=81&tipo[]=98&tipo[]=105&tipo[]=114&tipo[]=115',
          img: '/img/tipoActividad/construcciones.png',
          text: this.$t('frontend.tipo_actividad.construcciones'),
        },
        {
          url: 'filtro?tipo[]=25&tipo[]=28&tipo[]=29&tipo[]=75&tipo[]=76&tipo[]=82&tipo[]=83&tipo[]=85&tipo[]=113&tipo[]=117',
          img: '/img/tipoActividad/mesaTrabajo.png',
          text: this.$t('frontend.tipo_actividad.mesas_trabajo'),
        },
        {
          url: 'filtro?tipo[]=22&tipo[]=32&tipo[]=77&tipo[]=79&tipo[]=97&tipo[]=103',
          img: '/img/tipoActividad/infraestructura.png',
          text: this.$t('frontend.tipo_actividad.infraestructura'),
        },
        {
          url: 'filtro?tipo[]=23&tipo[]=30&tipo[]=31&tipo[]=33&tipo[]=34&tipo[]=35&tipo[]=36&tipo[]=45&tipo[]=46&tipo[]=47&tipo[]=49&tipo[]=52&tipo[]=53&tipo[]=56&tipo[]=58&tipo[]=59&tipo[]=62&tipo[]=89',
          img: '/img/tipoActividad/formacion.png',
          text: this.$t('frontend.tipo_actividad.espacios_formativos'),
        },
        {
          url: 'filtro?tipo[]=54&tipo[]=55&tipo[]=63&tipo[]=64&tipo[]=68&tipo[]=69&tipo[]=71&tipo[]=86&tipo[]=88&tipo[]=90',
          img: '/img/tipoActividad/encuentros.png',
          text: this.$t('frontend.tipo_actividad.encuentros'),
        },
        {
          url: 'filtro?tipo[]=43&tipo[]=96',
          img: '/img/tipoActividad/colecta.png',
          text: this.$t('frontend.tipo_actividad.colecta'),
        },
        {
          url: 'filtro?tipo[]=44&tipo[]=48&tipo[]=60&tipo[]=101&tipo[]=104',
          img: '/img/tipoActividad/eventos.png',
          text: this.$t('frontend.tipo_actividad.eventos_otros'),
        },
      ],
    };
  },
};
</script>

<style scoped>
/* Fila de categorías que envuelve (wrap) y se centra: sin flechas ni scroll,
   todas las categorías visibles a la vez. En mobile cae a varias filas
   centradas; en desktop entra en una sola fila. */
.categorias-tipos {
  row-gap: 8px;
}
</style>
