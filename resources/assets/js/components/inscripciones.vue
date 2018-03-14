<template>
</template>

<script>
    import axios from 'axios';

    export default {
        name: "inscripciones",
        methods: {
          es_inscripto: function (idActividad) {
            axios.get('/inscripciones/actividad/'+idActividad+'/inscripto').then(response => {
              if(response.data.idActividad) {
                window.location.href = '/actividades/' + response.data.idActividad
                
              }
            });
          }
        },
        mounted () {
          this.es_inscripto($('#idActividad').val())
          axios.get('/autenticado').then(response => {
                    window.addEventListener('loggedIn', (event) => {
                      this.es_inscripto($('#idActividad').val())
                    });
    			        	if(response.data == 'no') {
        							$('#btnShowModal').trigger('click')
    			        	}
                   })
                   .catch((error) => {
                       // Error
                       this.hasError = true;
                       if (error.response) {
                           // The request was made and the server responded with a status code
                           // that falls out of the range of 2xx
                           console.log(error.response.data);
                           console.log(error.response.status);
                           console.log(error.response.headers);
                       } else if (error.request) {
                           // The request was made but no response was received
                           // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                           // http.ClientRequest in node.js
                           console.log(error.request);
                       } else {
                           // Something happened in setting up the request that triggered an Error
                           console.log('Error', error.message);
                       }
                       console.log(error.config);
                   });
        },
    }
</script>