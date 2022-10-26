<template>
    <div>
        <article>
            <section class="text-center" :class="animation">
                <div v-if="formPosition < formGroup.length ">
                    <!-- <h2>{{ formGroup[formPosition].title }}</h2> -->
                    <h3>{{ formGroup[formPosition].pregunta }}</h3>
                    <b-form-group v-slot="{ ariaDescribedby }">
                        <b-form-radio-group id="radio-group-1" class="col-md-10" v-model="formGroup[formPosition].value"
                            :aria-describedby="ariaDescribedby" button-variant="outline-primary" name="radio-options"
                            buttons stacked>

                            <b-form-radio v-for="(respuesta, index) in formGroup[formPosition].respuestas"
                                :key="respuesta.value" v-model="formGroup[formPosition].value"
                                :aria-describedby="ariaDescribedby" @change="saveAnswer(respuesta, formPosition)" name="some-radios"
                                :value="index">{{ respuesta.text }}</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                </div>
                <div v-else-if="formPosition === formGroup.length">
                    <p class="mt-3 lead">
                        {{ $t('frontend.suscribe_so_we_get_in_touch') }}
                    </p>
                    <input 
                        class="m-2 text-center"
                        type="text"
                        name="email"
                        id="email"
                        :placeholder="$t('frontend.email')"
                        v-model="data.mail"
                        @blur="validateEmail"
                    >
                    <small class="form-text text-danger" v-if="!validation">{{ $t('frontend.email_validation_error') }}</small>
                    <div class="row justify-content-center">
                        <button class="btn btn-primary m-1" v-if="validation && data.mail != ''" @click="EndQuiz">
                            Ver Resultados
                        </button>
                    </div>
                </div>
                <div v-else>
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="card col-8 m-3 p-3" v-for="(perfil, index) in perfiles" v-if="perfil.id==perfilSelected">                     
                                <h1 class="text-primary">{{ perfil.perfil }}</h1>
                                <h4 class="text-secondary">{{ perfil.descripción }}</h4>
                            </div>
                        </div>
                        <a 
                            class="btn btn-primary"  
                            href="/actividades">
                            Ver Actividades
                        </a>
                    </div>
                </div>
                <div>
                    <button class="btn btn-secondary m-1" v-if="formPosition > 0 && perfilSelected == 0" @click="backStep">
                        Atrás
                    </button>
                    <button class="btn btn-light m-1" v-if="formPosition < formGroup.length " @click="nextStep">
                        Saltear
                    </button>
                </div>
            </section>
        </article>
    </div>
</template>
   
<script>
import Suscribe from '../suscribe';

export default {
    data: () => {
        return {
            formPosition: 0,
            animation: 'animate-in',
            perfilSelected: 0,
            validation: true,
            loading: false,
            submited: false,
            url: '/ajax/actividades/suscribe',
            data: {
                mail: '',
                perfil_seleccionado: "",
                tematica: "asdad",
                tiempo_disponible: "1111",
            },
            perfiles: [
                {
                    id: 1,
                    perfil: "Construcción y Encuestamiento",
                    descripción: "Sos una persona inquieta y de acción, te recomendamos sumarte algún finde a construir veredas o viviendas de emergencia, o a participar del proceso de encuestamiento pre construcción.",
                    valor: 0
                },
                {
                    id: 2,
                    perfil: "Formación Comunitaria",
                    descripción: "Sos una persona tranqui y paciente. Te recomendamos sumarte al voluntariado acompañando los ciclos de formación que se implementan en las comunidades: formación legal, política, en género y ciudad, o integración socio urbana.",
                    valor: 0
                },
                {
                    id: 3,
                    perfil: "Proyectos Comunitarios",
                    descripción: "Te gusta la acción pero también sabés tomarte las cosas con calma. Te recomendamos acompañar a las comunidades en la ejecución de proyectos comunitarios de infraestructura, medioamblientales y/o de acceso a servicios codo a codo con vecinos y vecinas.",
                    valor: 0
                },
            ],
            formGroup: [
                {
                    title: "Hábitos",
                    pregunta: "¿Qué tomas por la mañana?",
                    value: "",
                    model: "",
                    respuestas: [
                        { text: "Mate", value: "Mate", relate_to: "1" },
                        { text: "Café", value: "Café", relate_to: "2" },
                        { text: "Té", value: "Té", relate_to: "3" },]
                },
                {
                    title: "Habitos",
                    pregunta: "¿Que preferis?",
                    value: "",
                    model: "",
                    respuestas: [
                        { text: "Mirar una Serie", value: "Mirar una Serie", relate_to: "1" },
                        { text: "Leer un Libro", value: "Leer un libro", relate_to: "2" },
                        { text: "Mirar una peli", value: "Mirar una peli", relate_to: "3" },]
                },
                {
                    title: "Habitos",
                    pregunta: "¿Cuál dirias que es tu hobbie preferido?",
                    value: "",
                    model: "",
                    respuestas: [
                        { text: "Tomar algo con amistades", value: "Tomar algo con amistades", relate_to: "1" },
                        { text: "Hacer Ejercicio", value: "Hacer Ejercicio", relate_to: "2" },
                        { text: "Leer", value: "Leer", relate_to: "3" },]
                },
                {
                    title: "Habitos",
                    pregunta: "¿Cuál es tu la app que más usás?",
                    value: "",
                    model: "",
                    respuestas: [
                        { text: "TikTok", value: "TikTok", relate_to: "1" },
                        { text: "Twitter", value: "Twitter", relate_to: "2" },
                        { text: "Instagram", value: "Instagram", relate_to: "3" },]
                },
                {
                    title: "Habitos",
                    pregunta: "¿Qué temática te interesa más?",
                    value: "",
                    model: "data.tematica",
                    respuestas: [
                        { text: "Vivienda y hábitat", value: "Vivienda y hábitat", relate_to: "1" },
                        { text: "Género - Derecho", value: "Género - Derecho", relate_to: "2" },
                        { text: "Medioambiente", value: "Medioambiente", relate_to: "3" },]
                },
                {
                    title: "Habitos",
                    pregunta: "¿de cuanto tiempo disponés para voluntariar?",
                    value: "",
                    model: "data.tiempo_disponible",
                    respuestas: [
                        { text: "Entre 1 fin de semana y un mes", value: "Entre 1 fin de semana y un mes", relate_to: "1" },
                        { text: "Entre 1 y 3 meses", value: "Entre 1 y 3 meses", relate_to: "2" },
                        { text: "Entre 3 y 6 meses", value: "Entre 3 y 6 meses", relate_to: "3" },]
                },
            ]
        }
    },

    components: { Suscribe },

    methods: {
        EndQuiz(){
            //save data
            this.validateEmail();
            if (this.loading || !this.validation) { return; };
            this.loading = true;

            this.data.perfil_seleccionado = this.perfiles[this.perfilSelected].perfil;

            axios.post(this.url, this.data)
                .then(response => {
                    console.log(response);
                    this.submited=true;
                })
                .catch((error) => {
                    // Error
                    console.error('error en contenedor de tarjetas');
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

            this.loading = false;
            this.nextStep();
        },
        saveAnswer(respuesta, idPregunta) {
            if(this.formGroup[idPregunta].model){
               eval("this."+this.formGroup[idPregunta].model + " = "+ "'" + respuesta.text + "'"); 
            }
            this.nextStep();
        },
        nextStep() {
            this.animation = 'animate-out';
            setTimeout(() => {
                this.animation = 'animate-in';
                this.formPosition += 1;
                if(this.formPosition === this.formGroup.length){
                    this.setPuntaje();
                }
            }, 600);
        },
        backStep() {
            this.animation = 'animate-in';
            setTimeout(() => {
                this.animation = 'animate-in';
                this.formPosition -= 1;
            }, 600);
        },
        cleanPuntaje() {
            this.perfiles.forEach(perfil => {
                perfil.valor = 0;
            });
        },
        setPuntaje() {
            this.cleanPuntaje();
            this.formGroup.forEach(pregunta => {
                this.perfiles[pregunta.value].valor++;
            });
            this.setPerfil();
        },
        setPerfil() {
            let max = 0;
            this.perfiles.forEach(perfil => {
                if(perfil.valor>max){
                    this.perfilSelected = perfil.id;
                    max = perfil.valor;
                }
            });
        },
        validateEmail() {
            if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(this.data.mail)) {
                this.validation = true;
            } else {
                this.validation = false;
            }
            console.log(this.msg)
        },
    }
}

</script>
   
<style>
.animation-in {
    transform-origin: left;
    animation: in .6s ease-in-out;
}

.animation-out {
    transform-origin: bottom left;
    animation: out .6s ease-in-out;
}
</style>