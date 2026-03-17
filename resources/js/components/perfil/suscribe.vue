<template>
    <div class="card">
        <div class="card-body">
            <h2 class="text-uppercase mb-4 text-center">{{ $t('suscribe.title') }}</h2>
            <p class="m-1 text-center">{{ $t('suscribe.subtitle') }}</p>

            <!-- Información Personal -->
            <h4 class="mt-2">{{ $t('suscribe.personal_info') }}</h4>
            <form @submit.prevent="guardar">
                <div class="row">
                    <div class="col-md-4">
                    <input
                        class="form-control"
                        :placeholder="$t('suscribe.name')"
                        v-model="suscriptor.nombre"
                        required>
                    </div>
                    <div class="col-md-4">
                    <input
                        class="form-control"
                        :placeholder="$t('suscribe.lastname')"
                        v-model="suscriptor.apellido"
                        required>
                    </div>
                    <div class="col-md-4">
                    <input
                        class="form-control"
                        :placeholder="documentoLabel"
                        v-model="suscriptor.dni"
                        required>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4">
                        <input
                            class="form-control"
                            :placeholder="$t('suscribe.email')"
                            v-model="suscriptor.mail"
                            required>
                    </div>

                    <div class="col-md-4">
                        <select class="form-control" v-model="suscriptor.genero">
                            <option value="">{{ $t('suscribe.gender') }}</option>
                            <option value="F">{{ $t('frontend.gender_f') }}</option>
                            <option value="M">{{ $t('frontend.gender_m') }}</option>
                            <option value="X">{{ $t('frontend.gender_x') }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <datepicker
                            v-model="suscriptor.fecha_nacimiento"
                            :placeholder="$t('suscribe.fecha_de_nacimiento')"
                            id="nacimiento"
                            lang="es"
                            format="DD-MM-YYYY"
                        />
                    </div>
                </div>

                <div class="row mt-2">

                    <div class="col-md-4">
                        <VueTelInput
                            v-model="phoneNumber"
                            :preferredCountries="['mx','ar', 'co', 'pe', 'py', 'ur', 'br', 'cl']"
                            mode="international"
                            :defaultCountry="telefonoPaisIso"
                            :key="telefonoPaisIso"
                            :inputOptions="{
                                showDialCode: true,
                                placeholder: $t('suscribe.phone')
                            }"
                            :dropdownOptions="{
                                showFlags: true,
                            }"
                            ref="telInput"
                            :class="{ 'is-invalid': errores.telefono }"
                        />

                        <small v-if="errores.telefono" class="form-text text-danger">{{ $t('frontend.error') }}&nbsp;<br></small>
                    </div>

                    <div class="col-md-4">
                        <select id="localidad" v-model="suscriptor.idProvincia" class="form-control" required>
                            <option value="">{{ $t('suscribe.state') }}</option>
                            <option v-for="provincia in provincias" v-bind:value="provincia.id">
                                {{provincia.provincia}}
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <select id="localidad" v-model="suscriptor.idLocalidad" class="form-control" required>
                            <option value="">{{ $t('suscribe.city') }}</option>
                            <option v-for="localidad in localidades" v-bind:value="localidad.id">
                                {{localidad.localidad}}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-4">
                        <input
                            class="form-control"
                            placeholder="Instagram"
                            v-model="suscriptor.instagram">
                    </div>
                </div>

                <!-- Información Académica -->
                <h4 class="mt-3">{{ $t('suscribe.academic_info') }}</h4>
                <select class="form-control" v-model="suscriptor.ocupacion_actual">
                    <option value="">{{ $t('frontend.nivel_de_estudios') }}</option>
                    <option value="secundario"> {{ secundarioLabel }} </option>
                    <option value="universitario">{{ $t('frontend.universitario') }}</option>
                    <option value="posgrado"> {{ $t('frontend.posgrado') }} </option>
                </select>

                <!-- Información TECHO -->
                <h4 class="mt-3">{{ $t('suscribe.how_meet_techo') }}</h4>
                <select id="canal_contacto" v-model="suscriptor.canal_contacto" class="form-control" required>
                    <option v-bind:value="$t('frontend.social_networks')"> {{ $t('frontend.social_networks') }} </option>
                    <option v-bind:value="$t('frontend.advertisement_traditional_media')"> {{ $t('frontend.advertisement_traditional_media') }} </option>
                    <option v-bind:value="$t('frontend.outdoor_advertising')"> {{ $t('frontend.outdoor_advertising') }} </option>
                    <option v-bind:value="$t('frontend.website')"> {{ $t('frontend.website') }} </option>
                    <option v-bind:value="$t('frontend.known_person')"> {{ $t('frontend.known_person') }} </option>
                    <option v-bind:value="$t('frontend.email_campaign')"> {{ $t('frontend.email_campaign') }} </option>
                    <option v-bind:value="$t('frontend.street_intervention')"> {{ $t('frontend.street_intervention') }} </option>
                    <option v-bind:value="$t('frontend.event_collection_volunteer_campaign')"> {{ $t('frontend.event_collection_volunteer_campaign') }} </option>    
                </select>

                <div class="form-check mt-3">
                    <input
                    type="checkbox"
                    class="form-check-input"
                    v-model="suscriptor.experiencia_previa">
                    <label class="form-check-label">
                    {{ $t('suscribe.previous_experience') }}
                    </label>
                </div>

                <button
                    v-if="!guardado"
                    class="btn btn-primary btn-lg mt-4 w-100"
                    type="submit"
                    >
                        {{ $t('suscribe.submit') }}
                </button>
            </form>

            <div
                v-if="guardado"
                class="alert alert-success text-center mt-4 p-4"
                >
                <h4 class="mb-2">
                    {{ $t('suscribe.success_title') }}
                </h4>

                <p class="mb-1">
                    {{ $t('suscribe.success_message') }}
                </p>

                <p class="mb-0">
                    {{ $t('suscribe.success_extra') }}
                </p>
            </div>


            <small class="d-block text-center mt-2">
                {{ $t('suscribe.privacy_notice') }}
                <a href="https://www.techo.org/politicas-de-privacidad" target="_blank">
                {{ $t('suscribe.privacy_policy') }}
                </a>
            </small>
        </div>
    </div>
</template>

<script>
import { VueTelInput } from 'vue-tel-input';

export default {
    name: 'SuscripcionTecho',
    components: { VueTelInput },
    props: {
        pais: {
            type: Object,
            required: false,
            default: null
        }
    },
    data() {
        return {
            suscriptor: {
                nombre: '',
                apellido: '',
                genero: '',
                fecha_nacimiento: '',
                mail: '',
                telefono: '',
                idPais: '',
                idProvincia: '',
                idLocalidad: '',
                ocupacion_actual: '',
                canal_contacto: '',
                experiencia_previa: false,
                instagram: '',
                dni: '',
            },
            provincias: [],
            localidades: [],
            phoneNumber: '',
            previousCountry: '',
            telefonoPaisIso: null,
            guardado: false,
            errores: {},
        }
    },
    mounted: function(){
        this.removeUndefinedText();
        if (this.pais) {
            this.suscriptor.idPais = this.pais.id;
            this.traer_provincias();
            this.telefonoPaisIso = this.pais.iso2 ? this.pais.iso2 : null;
        }
    },

    computed: {
        documentoLabel() {
            if (!this.pais || !this.pais.abreviacion) {
                return this.$t('frontend.passport')
            }

            const key = `suscribe.dni_by_country.${this.pais.abreviacion}`

            // Si existe la traducción, úsala
            if (this.$te(key)) {
                return this.$t(key)
            }

            // Fallback
            return this.$t('frontend.passport')
        },
        secundarioLabel() {
            if (!this.pais || !this.pais.abreviacion) {
                return this.$t('frontend.secundario')
            }

            const key = `suscribe.secundario_by_country.${this.pais.abreviacion}`

            // Si existe la traducción, úsala
            if (this.$te(key)) {
                return this.$t(key)
            }

            // Fallback
            return this.$t('frontend.secundario')
        }
    },

    watch: {
        phoneNumber(val) {
            if (!val) {
                this.suscriptor.telefono = ''
                return
            }
            // Limpio espacios
            this.suscriptor.telefono = val.replace(/\s+/g, '')
        },
         telefonoPaisIso() {
            this.removeUndefinedText()
        },
        'suscriptor.idProvincia': function() {
            this.traer_localidades() 
        },
    },

    methods: {
        guardar(e) {
            const form = e.target;

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            this.errores.telefono = false;

            if (!this.suscriptor.telefono || this.suscriptor.telefono.length <= 6) {
                this.errores.telefono = true;
                return;
            }



            const payload = {
                ...this.suscriptor,
                fecha_nacimiento: this.formatFecha(this.suscriptor.fecha_nacimiento)
            }

            axios.post('suscribe', payload)
                .then(() => {
                    this.guardado = true;
            })
        },
        traer_provincias: function() {
          if(this.suscriptor.idPais) {
            axios.get('/ajax/paises/'+this.suscriptor.idPais+'/provincias').then(response => {
              this.provincias = response.data
              this.provinciaSeleccionada = null
              this.localidades = []
            })
          }
        },
        traer_localidades: function() {
          if(this.suscriptor.idPais && this.suscriptor.idProvincia) {
            axios.get('/ajax/paises/'+this.suscriptor.idPais+'/provincias/'+this.suscriptor.idProvincia+'/localidades').then(response => {
              this.localidades = response.data
              this.localidadSeleccionada = null
            })
          }
        },

        handleCountryChange(countryData) {
            const currentDialCode = this.previousCountry ? this.previousCountry.dialCode : '';
            const newDialCode = countryData.dialCode;

            if (currentDialCode == '' && this.suscriptor.telefono.startsWith('+')) {
                this.previousCountry =  countryData.dialCode;
            } else {
                // Extraer el número sin el código de país anterior
                const phoneNumberWithoutCountryCode = this.phoneNumber.replace('+' + currentDialCode, '').trim();

                // Actualizar el teléfono con el nuevo código de país
                this.suscriptor.telefono = '+' + newDialCode + phoneNumberWithoutCountryCode;
                this.phoneNumber = this.suscriptor.telefono;
                this.previousCountry = newDialCode;
            }
        },

        removeUndefinedText() {
            this.$nextTick(() => {
                const selection = this.$el.querySelector('.vti__selection')
                if (!selection) return

                selection.childNodes.forEach(node => {
                if (
                    node.nodeType === Node.TEXT_NODE &&
                    node.nodeValue &&
                    node.nodeValue.trim() === 'undefined'
                ) {
                    node.nodeValue = ''
                }
                })
            })
        },

        formatFecha(date) {
            if (!date) return null

            const yyyy = date.getFullYear()
            const mm = String(date.getMonth() + 1).padStart(2, '0')
            const dd = String(date.getDate()).padStart(2, '0')

            return `${yyyy}-${mm}-${dd}`
        },
    }
}
</script>

<style scoped>  
.is-invalid {
    border: 2px solid #dc3545 !important;
}
</style>