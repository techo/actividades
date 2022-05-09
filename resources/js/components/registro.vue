<template>
    <div>
        <div v-show="paso('email')">
            <div class="row">
                <div class="col-md-12">
                    <strong>{{ $t('frontend.register') }}</strong>  > {{ $t('frontend.personal_data') }} > {{ $t('frontend.finish') }}
                </div>
            </div>
            <div class="alert alert-danger hidden" v-bind:class="{'d-none': !message.danger}">
                <strong>{{message.text}}</strong>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h2> {{ $t('frontend.register') }}</h2>
                </div>
                <div class="col-md-6">
                    <label> {{ $t('frontend.step_1') }}</label>
                </div>
            </div>

            <hr>
            <div class="row">
                <div class="col-md-12">
                    <a class="btn facebook" @click="registro_facebook()">
                        <i class="fab fa-facebook-f"></i>&nbsp;&nbsp;{{ $t('frontend.register_facebook') }}
                    </a>
                </div>
                <div class="col-md-12 align-center">
                    <a class="btn google" @click="registro_google()">
                        <i class="fab fa-google"></i> {{ $t('frontend.register_google') }}
                    </a>
                </div>
            </div>
            <div class="row h-100 justify-content-center align-items-center">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.mail') }}*</label>
                        <input type="text" class="form-control" name="email" id="email" v-model="user.email">
                        <small class="form-text text-danger">{{validacion.email.texto}}&nbsp;<br></small>
                    </div>

                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.email.valido}">
                        <i class="fas fa-check text-success"></i>
                    </span>
                    <span v-bind:class="{'d-none':!validacion.email.invalido}">
                        <i class="fas fa-times text-danger"></i>
                    </span>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.create_password') }}</label>
                        <input type="password" class="form-control" name="pass" id="pass" v-model="user.pass">
                        <small class="form-text text-danger">{{validacion.pass.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.pass.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.pass.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <a class="btn btn-primary" @click="cambiar_paso()">
                        {{ $t('frontend.continue') }}
                    </a>
                </div>
            </div>
            <hr>
            

            
        </div>

        <div v-show="paso('personales')">
            <div class="row">
                <div class="col-md-12">
                    <strong>{{ $t('frontend.register') }}</strong>  > <strong> {{ $t('frontend.personal_data') }} </strong>> {{ $t('frontend.finish') }}
                </div>
            </div>
            <div class="alert alert-danger hidden" v-bind:class="{'d-none': !message.danger}">
                <strong>{{message.text}}</strong>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h2>{{ $t('frontend.almost_there') }}</h2>
                </div>
                <div class="col-md-6">
                    <label>{{ $t('frontend.step_2') }}</label>
                </div>
            </div>
            <div class="row h-100 justify-content-center align-items-center">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.name') }} *</label>
                        <input type="text" class="form-control" name="nombre" id="nombre" v-model="user.nombre">
                        <small class="form-text text-danger">{{validacion.nombre.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.nombre.valido}"><i class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.nombre.invalido}"><i class="fas fa-times text-danger"></i></span>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.surname') }} *</label>
                        <input type="text" class="form-control" name="apellido" id="apellido" v-model="user.apellido">
                        <small class="form-text text-danger">{{validacion.apellido.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.apellido.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.apellido.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>

            </div>

            <div class="row h-100 justify-content-center align-items-center">
                <div class="col-md-5">
                    <div class="form-group">
                        <label> {{ $t('frontend.birth_date') }} *</label>
                        <datepicker v-bind:placeholder="$t('frontend.date_placeholder')" v-model="user.nacimiento" id="nacimiento"
                                    lang="es" format="DD-MM-YYYY"></datepicker>
                        <small class="form-text text-danger">{{validacion.nacimiento.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.nacimiento.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.nacimiento.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>

                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.gender') }} *</label>
                        <b-form-group>
                            <b-form-radio-group id="radios2" v-model="user.sexo">
                                <b-form-radio value="F">{{ $t('frontend.gender_f') }}</b-form-radio>
                                <b-form-radio value="M">{{ $t('frontend.gender_m') }}</b-form-radio>
                                <b-form-radio value="X">{{ $t('frontend.gender_x') }}</b-form-radio>
                                <b-form-radio value="O">{{ $t('frontend.gender_o') }}</b-form-radio>
                            </b-form-radio-group>
                        </b-form-group>
                        <small class="form-text text-danger">{{validacion.sexo.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.sexo.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.sexo.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>
            </div>

            <div class="row h-100 justify-content-center align-items-center">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.passport') }} *</label>
                        <input type="text" class="form-control" name="dni" id="dni" v-model="user.dni">
                        <small class="form-text text-danger">{{validacion.dni.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.dni.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.dni.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.telephone') }} *</label>
                        <input type="text" class="form-control" name="telefono" id="telefono" v-model="user.telefono">
                        <small class="form-text text-danger">{{validacion.telefono.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.telefono.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.telefono.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>
            </div>


            <div class="row h-100 justify-content-center align-items-center">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.country') }} *</label>
                        <select id="pais" v-model="user.pais" class="form-control">
                            <option v-for="pais in paises" v-bind:value="pais.id">{{pais.nombre}}</option>
                        </select>
                        <small class="form-text text-danger">{{validacion.pais.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.pais.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.pais.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.state') }}</label>
                        <select id="provincia" v-model="user.provincia" class="form-control">
                            <option v-for="provincia in provincias" v-bind:value="provincia.id">
                                {{provincia.provincia}}
                            </option>
                        </select>
                        <small class="form-text text-danger">{{validacion.provincia.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.provincia.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.provincia.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>
            </div>

            <div class="row h-100 justify-content-center align-items-center">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>{{ $t('frontend.municipality') }}</label>
                        <select id="localidad" v-model="user.localidad" class="form-control">
                            <option v-for="localidad in localidades" v-bind:value="localidad.id">
                                {{localidad.localidad}}
                            </option>
                        </select>
                        <small class="form-text text-danger">{{validacion.localidad.texto}}&nbsp;<br></small>
                    </div>
                </div>
                <div class="col-md-1">
                    <span v-bind:class="{'d-none':!validacion.localidad.valido}"><i
                            class="fas fa-check text-success"></i></span>
                    <span v-bind:class="{'d-none':!validacion.localidad.invalido}"><i
                            class="fas fa-times text-danger"></i></span>
                </div>
                <div class="col-md-5">
                    <div class="form-group">

                    </div>
                </div>
                <div class="col-md-1">

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-check">
                        <input v-model="user.privacidad" class="form-check-input" type="checkbox" id="privacidad" required>
                        <label class="form-check-label" for="privacidad">
                            {{ $t('frontend.i_accept_the') }} <a href="https://www.techo.org/politica-de-privacidad" target="_blank"> {{ $t('frontend.privacy_policy') }}</a> *
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    <span v-bind:class="{'d-none':!validacion.privacidad.invalido}">
                        <i class="fas fa-times text-danger"></i>
                         {{ $t('frontend.error_privacy_policy') }}
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-check">
                        <input v-model="user.acepta_marketing" class="form-check-input" type="checkbox" id="acepta_marketing">
                        <label class="form-check-label" for="acepta_marketing">
                            {{ $t('frontend.techo_notifications_agreement') }}
                        </label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row pb-4">
                <div class="col-md-3 text-primary"><span v-bind:text="$t('frontend.go_back')"><a href='#' @click="paso_actual = 'email'"><i
                        class="fas fa-long-arrow-alt-left "></i> {{ $t('frontend.go_back') }}</a></span></div>
                <div class="col-md-3"><a class="btn btn-primary" @click="cambiar_paso()">{{ $t('frontend.continue') }}</a></div>
            </div>

        </div>
        <div v-show="paso('gracias')">
            <div class="row">
                <div class="col-md-12">
                    <strong>{{ $t('frontend.register') }}</strong>  > <strong> {{ $t('frontend.personal_data') }} </strong>> <strong>{{ $t('frontend.finish') }} </strong>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h2>{{ $t('frontend.welcome') }} TECHO</h2>
                </div>
                <div class="col-md-6">
                    <label>{{ $t('frontend.step_3') }}</label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    {{ $t('frontend.already_register')  }} <a href="/login">{{ $t('frontend.login') }}</a>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <a href="/actividades" class="btn btn-primary btn-lg">{{ $t('frontend.search_activities') }}</a>
                </div>
            </div>
        </div>
        <div v-show="paso('linkear')">
            <div class="row">
                <div class="col-md-12">
                    <strong>{{ $t('frontend.register') }}</strong> > <strong>{{ $t('frontend.link_to_rrss') }} </strong>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <h2>{{ $t('frontend.link_rrss_techo') }}</h2>
                </div>

                <div class="row">
                    <div class="col-md-3 text-primary"><i class="fas fa-long-arrow-alt-left "></i><a href="/">{{ $t('frontend.go_back') }}</a></div>
                    <div class="col-md-3"><a class="btn btn-primary" @click="confirma_linkear()">{{ $t('frontend.confirm') }}</a></div>
                </div>

            </div>
            <hr>
        </div>
  </div>
</template>

<script>
    import _ from 'lodash'
    export default {
      name: 'registro',
      data: function(){
        var data = {
          user: {},
          validacion: {},
          paso_actual: 'email',
          volver: true,
          paises: [],
          provincias: [],
          localidades: [],
          message: {
            danger: false,
            text: ''
          }
        }
        var campos = ['user','email','pass','nombre','apellido','nacimiento','sexo','dni','pais','provincia','localidad','telefono','facebook_id','google_id', 'privacidad'];
        for(var i in campos) {
          var campo = campos[i]
          data.user[campo] = '';
          if(this[campo]) data.user[campo] = this[campo];
          data.validacion[campo] = {
            texto: '',
            valido: false,
            invalido: false
          }
        }
        if(data.user.facebook_id || data.user.google_id) {
          data.paso_actual = 'personales'
          data.volver = false
        }
      	if(this.linkear) {
      	  data.paso_actual = 'linkear'
      	}
        return data
      },
      props: ['nombre','apellido','email','facebook_id','google_id','sexo','linkear'],
      mounted: function(){
        this.traer_paises()
      },
      watch: {
        'user.email': function() { this.validar_data('email') },
        'user.pass': function() { this.validar_data('pass') },
        'user.nombre': function() { this.validar_data('nombre') },
        'user.apellido': function() { this.validar_data('apellido') },
        'user.nacimiento': function() { this.validar_data('nacimiento') },
        'user.sexo': function() { this.validar_data('sexo') },
        // 'user.dni': function() { this.validar_data('dni') },
        'user.telefono': function() { this.validar_data('telefono') },
        'user.pais': function() { 
            this.validar_data('pais') 
            this.traer_provincias() 
        },
        'user.provincia': function() { this.traer_localidades() },
        'user.privacidad': function() { this.validar_data('privacidad')}
      },
      methods: {
        registro_facebook: function() {
          window.location.href = '/auth/facebook';
        },
        registro_google: function() {
          window.location.href = '/auth/google';
        },
        cambiar_paso: function (mod) {
          switch(this.paso_actual) {
            case 'email':
              if(!(this.validacion.email.valido && this.validacion.pass.valido)) return false
              this.paso_actual = 'personales'
              break
            case 'personales':
              axios.post('/ajax/usuario',this.user).then(response => {
                this.paso_actual = 'gracias'
                this.$parent.$refs.login.showValidUser(response.data.user);
                window.location.href = '/';
                if(response.data.login_callback) window.location.href = response.data.login_callback;
              }).catch((error) => {
                this.validar_data()
              });
            break
          }
        },
        confirma_linkear: function() {
          var media = '';
          var id = '';
          if(this.google_id) {
            media = 'google';
            id = this.google_id;
          }
          if(this.facebook_id) {
            media = 'facebook';
            id = this.facebook_id;
          }
          axios.put('/ajax/usuario/linkear', {
            media: media,
            id: id,
            email: this.email
          }).then(response => {
            if(response.data.login_callback) window.location.href = response.data.login_callback;
          })
        },
        paso: function (paso) {
          return paso == this.paso_actual
        },
        validar_data: _.debounce(function(prop) {
          var data = {}
          if(prop) {
            data[prop] = this.user[prop]
            this.validacion[prop].valido = false
            this.validacion[prop].invalido = false
	    if(prop == "pass") {
              data.google_id = this.user.google_id
              data.facebook_id = this.user.facebook_id
	    }
          } else {
            data = this.user
          }
          axios.get('/ajax/usuario/validar/create', {params: data})
          .then(response => {
            var params = response.data.params
            for(var i in params) {
              prop = params[i]
              this.validacion[prop].texto = ''
              if(this.user[prop]) {
                this.validacion[prop].valido = true
                this.validacion[prop].invalido = false
              }
            }
          })
          .catch(error => {
            var errors = error.response.data.errors
            for(var prop in errors) {
              this.validacion[prop].texto = errors[prop][0]
              this.validacion[prop].valido = false
              this.validacion[prop].invalido = true
            }
          })
        },500),
        traer_paises: function() {
          axios.get('/ajax/paises').then(response => {
            this.paises = response.data
          })
        },
        traer_provincias: function() {
          if(this.user.pais) {
            axios.get('/ajax/paises/'+this.user.pais+'/provincias').then(response => {
              this.provincias = response.data
            })
          }
        },
        traer_localidades: function() {
          if(this.user.pais && this.user.provincia) {
            axios.get('/ajax/paises/'+this.user.pais+'/provincias/'+this.user.provincia+'/localidades').then(response => {
              this.localidades = response.data
            })
          }
        }
      }
    }
</script>

<style scoped>
    a.btn-primary {
        color: #ffffff;
    }
</style>