<template>
  <div>
    <simple-alert ref="alert"></simple-alert>

    <div class="row" style="margin-bottom: 10px;">
      <div class="col-md-12">
        <a
          v-if="publicUrl"
          :href="publicUrl"
          target="_blank"
          class="btn btn-default pull-right"
        >
          <i class="fa fa-external-link"></i> {{ $t('backend.view_public_link') }}
        </a>
      </div>
    </div>

    <form @submit.prevent="guardar">
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>{{ $t('backend.name') }} *</label>
            <input v-model="form.nombre" class="form-control" required>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>{{ $t('backend.type') }}</label>
            <select v-model="form.tipo" class="form-control">
              <option value="">-- {{ $t('backend.select') }} --</option>
              <option value="colecta">{{ $t('backend.campaign_tipo_options.colecta') }}</option>
              <option value="captacion">{{ $t('backend.campaign_tipo_options.captacion') }}</option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label>{{ $t('backend.description') }}</label>
        <textarea v-model="form.descripcion" class="form-control" rows="3"></textarea>
      </div>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label>{{ $t('backend.start_date') }}</label>
            <input v-model="form.fecha_inicio" type="date" class="form-control">
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label>{{ $t('backend.end_date') }}</label>
            <input v-model="form.fecha_fin" type="date" class="form-control">
          </div>
        </div>
      </div>

      <div class="form-group">
        <div class="checkbox">
          <label>
            <input type="checkbox" v-model="form.activa">
            {{ $t('backend.active') }}
          </label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" :disabled="guardando">
        <span v-if="guardando"><i class="fa fa-spinner fa-spin"></i> {{ $t('backend.saving') }}</span>
        <span v-else>{{ $t('backend.save') }}</span>
      </button>

      <span v-if="guardadoOk" class="text-success ml-3">
        <i class="fa fa-check"></i> {{ $t('backend.saved') }}
      </span>
    </form>

    <!-- Imagen hero -->
    <div v-if="campanaId" style="margin-top: 30px;">
      <hr>
      <h4>{{ $t('backend.hero_image') }}</h4>

      <div v-if="imagenActual" style="margin-bottom: 12px;">
        <img :src="imagenActual" style="max-width: 100%; max-height: 300px; display: block; border: 1px solid #ddd; border-radius: 4px;">
      </div>
      <div v-else class="text-muted" style="margin-bottom: 12px;">
        {{ $t('backend.no_image') }}
      </div>

      <div class="form-group">
        <label>{{ $t('backend.upload_image') }}</label>
        <input type="file" accept="image/*" @change="subirImagen" :disabled="subiendo">
        <span v-if="subiendo" class="text-muted">
          <i class="fa fa-spinner fa-spin"></i> {{ $t('backend.uploading') }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import Simplert from 'vue2-simplert'

export default {
  components: { Simplert },
  props: {
    campanaId: {
      type: Number,
      default: null,
    },
    initialData: {
      type: Object,
      default: null,
    },
    publicUrl: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      form: {
        nombre:       '',
        descripcion:  '',
        tipo:         '',
        fecha_inicio: null,
        fecha_fin:    null,
        activa:       true,
      },
      imagenActual: null,
      guardando:    false,
      guardadoOk:   false,
      subiendo:     false,
    }
  },
  mounted() {
    if (this.initialData) {
      this.form = Object.assign({}, this.form, {
        nombre:       this.initialData.nombre       || '',
        descripcion:  this.initialData.descripcion  || '',
        tipo:         this.initialData.tipo         || '',
        fecha_inicio: this.initialData.fecha_inicio || null,
        fecha_fin:    this.initialData.fecha_fin    || null,
        activa:       this.initialData.activa !== undefined ? this.initialData.activa : true,
      })
      this.imagenActual = this.initialData.imagen || null
    }
  },
  methods: {
    guardar() {
      this.guardando  = true
      this.guardadoOk = false

      const url    = this.campanaId ? `/admin/ajax/campanas/${this.campanaId}` : '/admin/ajax/campanas'
      const method = this.campanaId ? 'put' : 'post'

      axios[method](url, this.form)
        .then(response => {
          this.guardadoOk = true
          if (!this.campanaId) {
            window.location.href = `/admin/campanas/${response.data.id}`
          }
        })
        .catch(() => {
          alert(this.$t('backend.error_guardando'))
        })
        .finally(() => {
          this.guardando = false
        })
    },
    subirImagen(event) {
      const file = event.target.files[0]
      if (!file) return

      const formData = new FormData()
      formData.append('imagen', file)

      this.subiendo = true
      axios.post(`/admin/ajax/campanas/${this.campanaId}/imagen`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
        .then(response => {
          this.imagenActual = response.data.imagen
        })
        .catch(() => {
          alert(this.$t('backend.error_guardando'))
        })
        .finally(() => {
          this.subiendo = false
          event.target.value = ''
        })
    },
  },
}
</script>
