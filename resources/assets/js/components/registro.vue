<template>
  <div>
    <div v-show="paso(1)">
      <div class="row">
        <div class="col-md-12">
          <strong>Registrate</strong> > Datos personales > Finalizar    
        </div>
      </div>
      <div class="alert alert-danger hidden" v-bind:class="{'d-none': !message.danger}">
        <strong>{{message.text}}</strong>
      </div>
      <div class="row">
        <div class="col-md-4">
          <h2>Registrate como voluntario.</h2>
        </div>
        <div class="col-md-8">
          <strong>PASO 1/3</strong>   
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-md-12"><label>EMAIL</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="email" id="email" v-model="user.email">
          <small class="form-text text-muted">{{validacion.email.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.email.valido}"><i class="fas fa-check"></i></span>
          <span v-bind:class="{'d-none':!validacion.email.checking_email}"><i class="far fa-clock"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>CREAR UNA CONTRASEÑA</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="password" class="form-control" name="pass" id="pass" v-model="user.pass">
          <small class="form-text text-muted">{{validacion.pass.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.pass.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-3"><a class="btn btn-primary" @click="cambiar_paso(+1)">CONTINUA</a></div>
      </div>

    </div>
    <div v-show="paso(2)">
        <div class="row">
            <div class="col-md-12">
          <strong>Registrate</strong> > <strong>Datos personales</strong> > Finalizar   
        </div>
      </div>
      <div class="alert alert-danger hidden" v-bind:class="{'d-none': !message.danger}">
        <strong>{{message.text}}</strong>
      </div>
      <div class="row">
        <div class="col-md-4">
          <h2>¡Ya casi terminamos!</h2>
        </div>
        <div class="col-md-8">
          <strong>PASO 2/3</strong>   
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>NOMBRE</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="nombre" id="nombre" v-model="user.nombre">
          <small class="form-text text-muted">{{validacion.nombre.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.nombre.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>APELLIDO</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="apellido" id="apellido" v-model="user.apellido">
          <small class="form-text text-muted">{{validacion.apellido.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.apellido.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>NACIMIENTO</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <datepicker placeholder="Select Date" v-model="user.nacimiento" id="nacimiento"></datepicker>
          <small class="form-text text-muted">{{validacion.nacimiento.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.nacimiento.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>SEXO</label></div>
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
          <span v-bind:class="{'d-none':!validacion.sexo.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>
      <div class="row">
        <small class="form-text text-muted">{{validacion.sexo.texto}}&nbsp;<br></small>
      </div>    
      <div class="row">
        <div class="col-md-12"><label>NRO. DE DNI</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="dni" id="dni" v-model="user.dni">
          <small class="form-text text-muted">{{validacion.dni.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.dni.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>PASAPORTE</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="pasaporte" id="pasaporte" v-model="user.pasaporte">
          <small class="form-text text-muted">{{validacion.pasaporte.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.pasaporte.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>PAIS</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <select id="pais" v-model="user.pais" class="form-control">
            <option v-for="pais in paises" v-bind:value="pais.id">{{pais.nombre}}</option>
          </select>
          <small class="form-text text-muted">{{validacion.pais.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.pais.valido}"><i class="fas fa-check"></i></span>
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
          <small class="form-text text-muted">{{validacion.provincia.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.provincia.valido}"><i class="fas fa-check"></i></span>
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
          <small class="form-text text-muted">{{validacion.localidad.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.localidad.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12"><label>TELEFONO</label></div>
      </div>
      <div class="row">
        <div class="col-md-5">
          <input type="text" class="form-control" name="telefono" id="telefono" v-model="user.telefono">
          <small class="form-text text-muted">{{validacion.telefono.texto}}&nbsp;<br></small>
        </div>
        <div class="col-md-7">
          <span v-bind:class="{'d-none':!validacion.telefono.valido}"><i class="fas fa-check"></i></span>
        </div>
      </div>

      <hr>
      <div class="row">
        <div class="col-md-3 text-primary"><i class="fas fa-long-arrow-alt-left "></i><a @click="cambiar_paso(-1)"> Volver</a></div>
        <div class="col-md-3"><a class="btn btn-primary" @click="cambiar_paso(+1)">CREAR CUENTA</a></div>
      </div>

    </div>
    <div v-show="paso(3)">
        <div class="row">
            <div class="col-md-12">
          <strong>Registrate</strong> > <strong>Datos personales</strong> > <strong>Finalizar</strong>
        </div>
      </div>
        <div class="row">
            <div class="col-md-4">
          <h2>Bienvenid@ a Techo</h2>
        </div>
            <div class="col-md-8">
          <strong>PASO 3/3</strong>   
        </div>
      </div>
      <hr>    
    </div>
  </div>
</template>

<script>
    export default {
      name: 'registro',
      data: function(){
        var data = {
          user: {},
          validacion: {},
          paso_actual: 1,
          paises: [],
          provincias: [],
          localidades: [],
          message: {
            danger: false,
            text: ''
          }
        }
        var campos = ['user','email','pass','nombre','apellido','nacimiento','sexo','dni','pasaporte','pais','provincia','localidad','telefono'];
        for(var i in campos) {
          var campo = campos[i]
          data.user[campo] = ''
          data.validacion[campo] = {
            texto: '',
            valido: ''
          }
        }
        data.validacion.email.checking_email = false,
        data.validacion.email.last_value = ''
        return data
      },
      mounted: function(){
        this.popular_pais()
      },
      watch: {
        user: {
          handler: function(nuevo, viejo) {
            if(this.validacion.email.last_value != this.user.email) this.validar_email()
            this.validar_pass()
            this.validar_nombre()
            this.validar_apellido()
            this.validar_nacimiento()
            this.validar_sexo()
            this.validar_dni()
            this.validar_pasaporte()
            this.validar_pais()
            this.validar_provincia()
            this.validar_localidad()
            this.validar_telefono()
            this.traer_provincias()
            this.traer_localidades()
          },
          deep: true
        }
      },
      methods: {
        cambiar_paso: function (mod) {
          if(mod < 0) {
            this.paso_actual = this.paso_actual + mod
            return false
          }
          switch(this.paso_actual) {
            case 1:
              if(!(this.validacion.email.valido && this.validar_pass())) return false
              this.paso_actual = this.paso_actual + mod
              break
            case 2:
              if(this.validar_nombre() && this.validar_apellido() && this.validar_nacimiento() && this.validar_sexo() && this.validar_dni() && this.validar_telefono()) {
                axios.post('/ajax/usuario',this.user).then(response => {
                  console.log(response)
                  this.paso_actual = this.paso_actual + mod
                }).catch((error) => {
                  this.hasError = true;
                  if (error.response) {
                    console.log(error.response.data);
                    console.log(error.response.status);
                    console.log(error.response.headers);
                  } else if (error.request) {
                    console.log(error.request);
                  } else {
                    console.log('Error', error.message);
                  }
                  console.log(error.config);
                  this.message.text = error.response.data.message
                  this.message.danger = true
                  if(error.response.data.field == 'email') {
                    $('#email').addClass('border-danger')
                  }
                });
              } else {
                return false
              }
              break
          }
        },
        paso: function (paso) {
          return paso == this.paso_actual 
        },
        popular_pais: function() {
          axios.get('/ajax/paises').then(response => {
            this.paises = response.data
          })
        },
        validar_email: function() {
          this.validacion.email.texto = ''
          self = this
          this.validacion.email.valido = false
          if(!this.user.email) return false
          this.validacion.email.texto = 'Debe ser una direccion de e-mail valido'
          var re = /\S+@\S+\.\S+/;
          var res = re.test(this.user.email)
          if(res) this.validacion.email.texto = ''
          if(res && this.validacion.email.last_value != this.user.email) {
            this.validacion.email.last_value = this.user.email
            this.validacion.email.checking_email = true
            this.validacion.email.texto = 'Validando si el e-mail ya esta registrado...'
            axios.get('/ajax/usuario/valid_new_mail?email=' + this.user.email).then(response => {
              console.log(response.data, response.data != 0, this.validacion.email.valido)
              self.validacion.email.checking_email = false
              if(response.data == 0) {
                self.validacion.email.valido = true
                self.validacion.email.texto = ''
              } else {
                self.validacion.email.valido = false
                self.validacion.email.texto = 'Esta direccion ya esta registrada como usuario'
              }
            })
          }
          return self.validacion.email.valido
        },
        validar_pass: function() {
          this.validacion.pass.valido = false
          this.validacion.nombre.texto = 'Este dato es requerido'
          if(!this.user.pass) return false
          this.validacion.pass.texto = 'La contraseña debe tener mas de 8 caracteres'
          var res = this.user.pass.length >= 8
          if(res) this.validacion.pass.texto = ''
          if(res) this.validacion.pass.valido = true
          return this.validacion.pass.valido
        },
        validar_nombre: function() {
          this.validacion.nombre.valido = false
          this.validacion.nombre.texto = 'Este dato es requerido'
          if(!this.user.nombre) return false
          var res = this.user.nombre.trim().length > 0
          if(res) this.validacion.nombre.texto = ''
          if(res) this.validacion.nombre.valido = true
          return this.validacion.nombre.valido
        },
        validar_apellido: function() {
          this.validacion.apellido.valido = false
          this.validacion.apellido.texto = 'Este dato es requerido'
          if(!this.user.apellido) return false
          var res = this.user.apellido.trim().length > 0
          if(res) this.validacion.apellido.texto = ''
          if(res) this.validacion.apellido.valido = true
          return this.validacion.apellido.valido
        },
        validar_nacimiento: function() {
          this.validacion.nacimiento.valido = false
          this.validacion.nacimiento.texto = 'Este dato es requerido'
          if(!this.user.nacimiento) return false
          this.validacion.nacimiento.texto = ''
          this.validacion.nacimiento.valido = true
          return this.validacion.nacimiento.valido
        },
        validar_sexo: function() {
          this.validacion.sexo.valido = false
          this.validacion.sexo.texto = 'Este dato es requerido'
          if(!this.user.sexo) return false
          this.validacion.sexo.texto = ''
          this.validacion.sexo.valido = true
          return this.validacion.sexo.valido
        },
        validar_dni: function() {
          this.validacion.dni.valido = false
          this.validacion.dni.texto = 'Este dato es requerido'
          if(!this.user.dni) return false
          var re = /^[\d- .,]+$/;
          var res = re.test(this.user.dni);
          if(!res) {
            this.validacion.dni.texto = 'Ingrese un documento valido'
          }
          if(res) this.validacion.dni.texto = ''
          if(res) this.validacion.dni.valido = true
          return this.validacion.dni.valido
        },
        validar_pasaporte: function() {
          this.validacion.pasaporte.valido = false
          var re = /^[\d- .,]+$/;
          var res = re.test(this.user.pasaporte);
          if(!res) {
            this.validacion.pasaporte.texto = 'Ingrese un documento valido'
          }
          if(res) this.validacion.pasaporte.texto = ''
          if(res) this.validacion.pasaporte.valido = true
          return this.validacion.pasaporte.valido
        },
        validar_pais: function(){
          this.validacion.pais.valido = false
          var res = this.user.pais != ''
          if(res) this.validacion.pais.valido = true
          return this.validacion.pais.valido
        },
        validar_provincia: function(){
          this.validacion.provincia.valido = false
          var res = this.user.provincia != ''
          if(res) this.validacion.provincia.valido = true
          return this.validacion.provincia.valido
        },
        validar_localidad: function(){
          this.validacion.localidad.valido = false
          var res = this.user.localidad != ''
          if(res) this.validacion.localidad.valido = true
          return this.validacion.localidad.valido
        },
        validar_telefono: function() {
          this.validacion.telefono.valido = false
          var re = /^[\d- .,]+$/;
          var res = re.test(this.user.telefono);
          if(!res) {
            this.validacion.telefono.texto = 'Ingrese un numero valido'
          }
          if(res) this.validacion.telefono.texto = ''
          if(res) this.validacion.telefono.valido = true
          return this.validacion.telefono.valido
        },
        traer_provincias: function() {
          if(this.user.pais) {
            axios.get('/ajax/pais/'+this.user.pais+'/provincias').then(response => {
              this.provincias = response.data
            })
          }
        },
        traer_localidades: function() {
          if(this.user.pais && this.user.provincia) {
            axios.get('/ajax/pais/'+this.user.pais+'/provincia/'+this.user.provincia+'/localidades').then(response => {
              this.localidades = response.data
            })
          }
        }
      }
    }
</script>