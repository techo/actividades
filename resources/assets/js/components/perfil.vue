<template>
  <div>
    <div>
      <div class="row">
        <div class="col-md-4">
          <h2>Datos de perfil.</h2>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <h4>Estos son tus datos de usuario de Techo</h4>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
          tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam,
          quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo.
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-md-12"><label>EMAIL*</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="email" id="email" v-model="user.email">
          <small class="form-text text-danger">{{validacion.email.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.email.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.email.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-md-12"><label>NOMBRE*</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="nombre" id="nombre" v-model="user.nombre">
          <small class="form-text text-danger">{{validacion.nombre.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.nombre.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.nombre.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>APELLIDO*</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="apellido" id="apellido" v-model="user.apellido">
          <small class="form-text text-danger">{{validacion.apellido.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.apellido.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.apellido.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>NACIMIENTO*</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <datepicker placeholder="Select Date" v-model="user.nacimiento" id="nacimiento"  language="es"></datepicker>
          <small class="form-text text-danger">{{validacion.nacimiento.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.nacimiento.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.nacimiento.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>SEXO*</label></div>
      </div>
      <div class="row">
        <div class="col-md-3">
          <input type="radio" class="form-control" id="fem" value="F" v-model="user.sexo">
          <label for="fem">Femenino</label>
        </div>
        <div class="col-md-3">
          <input type="radio" class="form-control" id="mas" value="M" v-model="user.sexo">
          <label for="mas">Masculino</label>
        </div>
        <div class="col-md-3">
          <span v-bind:class="{'d-none':!validacion.sexo.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.sexo.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <div class="row">
        <small class="form-text text-danger">{{validacion.sexo.texto}}&nbsp;<br></small>
      </div>    
      <div class="row">
        <div class="col-md-12"><label>NRO. DE DNI / PASAPORTE*</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="dni" id="dni" v-model="user.dni">
          <small class="form-text text-danger">{{validacion.dni.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.dni.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.dni.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>PAIS*</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <select id="pais" v-model="user.pais" class="form-control">
            <option v-for="pais in paises" v-bind:value="pais.id">{{pais.nombre}}</option>
          </select>
          <small class="form-text text-danger">{{validacion.pais.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.pais.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.pais.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>PROVINCIA</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <select id="pais" v-model="user.provincia" class="form-control">
            <option v-for="provincia in provincias" v-bind:value="provincia.id">{{provincia.provincia}}</option>
          </select>
          <small class="form-text text-danger">{{validacion.provincia.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.provincia.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.provincia.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>LOCALIDAD</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <select id="pais" v-model="user.localidad" class="form-control">
            <option v-for="localidad in localidades" v-bind:value="localidad.id">{{localidad.localidad}}</option>
          </select>
          <small class="form-text text-danger">{{validacion.localidad.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.localidad.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.localidad.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>TELEFONO*</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="telefono" id="telefono" v-model="user.telefono">
          <small class="form-text text-danger">{{validacion.telefono.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.telefono.valido}"><i class="fas fa-check text-success"></i></span>
          <span v-bind:class="{'d-none':!validacion.telefono.invalido}"><i class="fas fa-times text-danger"></i></span>
        </div>
      </div>

      <hr>
      <div class="row">
        <div class="col-md-3 text-primary"><span v-show='volver'><a href='#' @click="paso_actual = 'email'"><i class="fas fa-long-arrow-alt-left "></i> Volver</a></span></div>
        <div class="col-md-3"><a class="btn btn-primary" @click="guardar()">GUARDAR</a></div>
      </div>
      <hr>    
    </div>
  </div>
</template>

<script>
    import _ from 'lodash'

    export default {
      name: 'perfil',
      data: function(){
        var data = {
          user: JSON.parse(this.usuario),
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
        var campos = ['id','email','nombre','apellido','nacimiento','sexo','dni','pais','provincia','localidad','telefono','facebook_id','google_id'];
        for(var i in campos) {
          var campo = campos[i]
          if(!data.user[campo]) data.user[campo] = ''
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
      props: ['usuario'],
      mounted: function(){
        this.traer_paises()
      },
      watch: {
        'user.email': function() { this.validar_data('email') },
        'user.nombre': function() { this.validar_data('nombre') },
        'user.apellido': function() { this.validar_data('apellido') },
        'user.nacimiento': function() { this.validar_data('nacimiento') },
        'user.sexo': function() { this.validar_data('sexo') },
        'user.dni': function() { this.validar_data('dni') },
        'user.telefono': function() { this.validar_data('telefono') },
        'user.pais': function() { this.traer_provincias() },
        'user.provincia': function() { this.traer_localidades() }
      },
      methods: {
        registro_facebook: function() {
          window.location.href = 'https://actividades.techo.org/auth/facebook';
        },
        registro_google: function() {
          window.location.href = 'https://actividades.techo.org/auth/google';
        },
        guardar: function () {
            	axios.put('/ajax/usuario',this.user).then(response => {
            }).catch((error) => {
				this.validar_data()
            });
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
            data.id = this.user.id
            this.validacion[prop].valido = false
            this.validacion[prop].invalido = false
          } else {
            data = this.user
          }
          axios.get('/ajax/usuario/validar', {params: data})
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
