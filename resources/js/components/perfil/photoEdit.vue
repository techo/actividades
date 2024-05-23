<template>
    <div v-if="openPhotoEdit">
      <input
        ref="input"
        hidden
        type="file"
        name="image"
        accept="image/*"
        @change="setImage"
      />
      <div class="content">
        <section class="cropper-area">
          <div class="img-cropper">
            <vue-cropper
              ref="cropper"
              :aspect-ratio="1 / 1"
              :src="photoPerfil"
              preview=".preview"
            />
          </div>
          <div class="actions">
            <a
              href="#"
              role="button"
              @click.prevent="cropImage"
            >
            <i class="fa fa-crop"></i> {{ $t('frontend.crop' ) }}
            </a>
            <a
              href="#"
              role="button"
              @click.prevent="showFileChooser"
            >
            <i class="fa fa-upload"></i>{{ $t('frontend.upload_new' ) }}
            </a>
          </div>
  
        </section>
      </div>
    </div>
  </template>
  
  <script>
  import VueCropper from 'vue-cropperjs';
  import 'cropperjs/dist/cropper.css';
  
  export default {
    name: 'photoEdit',

    props: ['openPhotoEdit', 'photoPerfil'], 

    components: {
      VueCropper,
    },
    data() {
      return {
        cropImg: '',
        imgSrc: '',
        data: null,
      };
    },
    methods: {
      cropImage() {
        const cropper = this.$refs.cropper;
        const croppedImage = cropper.getCroppedCanvas().toDataURL('image/jpeg');
        const croppedCanvas = cropper.getCroppedCanvas();


        this.$refs.cropper
        .getCroppedCanvas({
          width: 200,
          height: 200
        })
        .toBlob(blob => {
            const formData = new FormData();


          formData.append("photo", blob, 'avatar');
            const headers = { 'Content-Type': 'multipart/form-data' };
            axios.post('/ajax/usuario/perfil/cambiar_photo', formData, { headers }).then(response => {
                this.guardo = true;
                this.formDirty = false;
                this.$emit('updatePhoto', response.data.newUrl);
            }).catch((error) => {
            });
        });
      },
      setImage(e) {
        const file = e.target.files[0];
  
        if (file.type.indexOf('image/') === -1) {
          alert('Please select an image file');
          return;
        }
  
        if (typeof FileReader === 'function') {
          const reader = new FileReader();
  
          reader.onload = (event) => {
            this.imgSrc = event.target.result;
            // rebuild cropperjs with the updated source
            this.$refs.cropper.replace(event.target.result);
          };
  
          reader.readAsDataURL(file);
        } else {
          alert('Sorry, FileReader API not supported');
        }
      },
      showFileChooser() {
        this.$refs.input.click();
      },
    },
  };
  </script>
