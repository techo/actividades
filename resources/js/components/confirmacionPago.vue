<template>
    <div >
            <div class="row m-2 justify-content-center">
                <a v-if="voucher != ''" :href="'/'+voucher" target="_blank" class="col-md-4 text-center"> 
                    <img class="imagen-miniatura-redonda" :src="'/'+voucher" alt="Foto">
                </a>
                <button class="btn btn-light col-md-8" @click="selectVoucher" ><i class="fa fa-upload p-1"></i>{{ $t('frontend.upload_voucher') }}</button>
            </div>
            <input hidden type="file" @change="guardar_voucher" ref="voucher_input">
            <div class="row">
              <span class="text-secondary text-centered border-0 m-2 p-1">{{ nombre_voucher }}</span>
            </div>

            <div class="row my-2" v-if="nombre_voucher != null">
                  <div class="col-md-12">
                    <button type="button" 
                        class="btn btn-primary form-control" 
                        @click="submitVoucher" >
                        <span aria-hidden="true">
                            {{ $t('frontend.save') }}
                        </span>
                    </button>
                  </div>
                </div>

                <div class="row my-2" >
                  <div class="col-md-12" v-if="guardo">
                    <button type="button" disable
                        class="btn btn-success form-control" 
                        @click="submitVoucher" >
                        <span aria-hidden="true">
                            {{ $t('frontend.changes_success') }}
                        </span>
                    </button>
                  </div>
                  <div class="col-md-12" v-if="!guardo && !(nombre_voucher==null)">
                    <button type="button" disable
                      class="btn btn-secundary form-control" 
                      @click="redirectToIndex">
                      <span aria-hidden="true">
                          {{ $t('frontend.confirm_later') }}
                      </span>
                    </button>
                  </div>
                </div>
    </div>
</template>

<script>
    import axios from 'axios';
    export default {
        name: "confirmacionPago",
        props: ['id','csrf_token', 'voucher'],
        data: function(){
          return {
            nombre_voucher: null,
            new_voucher: null,
            voucherUrl: null,
            guardo: false,
          }
        },
        methods: {
          guardar_voucher(event) {
            this.new_voucher = this.$refs.voucher_input.files[0];
            this.nombre_voucher = this.$refs.voucher_input.files[0].name;
          },

          selectVoucher: function () {
            this.$refs.voucher_input.click();
          },
          submitVoucher: function () {
            const formData = new FormData();
            formData.append('voucher', this.new_voucher);
            formData.append('idInscripcion', this.id);
            const headers = { 'Content-Type': 'multipart/form-data' };
            axios.post('/ajax/inscripcion/voucherPago', formData, { headers }).then(response => {
              this.guardo = true;
              this.nombre_voucher = null;
              this.voucher = response.data.voucherURL;
            }).catch((error) => {
            });
          },
          redirectToIndex() {
              window.location.href = '/';
          },
        }
    }
</script>
