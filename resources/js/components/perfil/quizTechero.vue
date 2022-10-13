<template>
    <div>
        <article>
            <section class="text-center" :class="animation">
                <div v-if="formPosition < formGroup.length ">
                    <h2>{{ formGroup[formPosition].title }}</h2>
                    <h3>{{ formGroup[formPosition].pregunta }}</h3>
                    <b-form-group v-slot="{ ariaDescribedby }">
                        <b-form-radio-group id="radio-group-1" class="col-md-10" v-model="formGroup[formPosition].value"
                            :aria-describedby="ariaDescribedby" button-variant="outline-primary" name="radio-options"
                            buttons stacked>

                            <b-form-radio v-for="(respuesta, index) in formGroup[formPosition].respuestas"
                                :key="respuesta.value" v-model="formGroup[formPosition].value"
                                :aria-describedby="ariaDescribedby" @change="nextStep(respuesta)" name="some-radios"
                                :value="index">{{ respuesta.text }}</b-form-radio>
                        </b-form-radio-group>
                    </b-form-group>
                </div>
                <div v-else>
                    <div class="container">
                        <div class="row">
                            <div class="col-sm" v-for="(perfil, index) in perfiles" v-if="perfil.id>0">
                                <h1 class="text-primary">{{ perfil.valor }}</h1>
                                <h2 class="text-primary">{{ perfil.perfil }}</h2>
                                <p>{{ perfil.descripción }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <button class="btn btn-secondary" v-if="formPosition > 0 " @click="backStep">
                        Atrás
                    </button>
                    <button class="btn btn-light" v-if="formPosition +1 < formGroup.length " @click="nextStep">
                        Saltear
                    </button>

                    <button class="btn btn-primary" v-if="formPosition +1 === formGroup.length " @click="setPuntaje">
                        Calcular
                    </button>
                </div>
            </section>
        </article>
    </div>
</template>
   
<script>
export default {
    data: () => {
        return {
            formPosition: 0,
            animation: 'animate-in',
            perfiles: [
                {
                    id: 1,
                    perfil: "Actividades de *construcción y encuestamiento*",
                    descripción: "Sos una persona inquieta y de acción, te recomendamos sumarte algún finde a construir veredas o viviendas de emergencia, o a participar del proceso de encuestamiento pre cosntrucción.",
                    valor: 0
                },
                {
                    id: 2,
                    perfil: "Actividades de *formación comunitaria*",
                    descripción: "Sos una persona tranqui y paciente. Te recomendamos sumarte al voluntariado acompañando los ciclos de formación que se implementan en las comunidades: formación legal, política, en género y ciudad, o integración socio urbana.",
                    valor: 0
                },
                {
                    id: 3,
                    perfil: "Ser parte de la ejecución de *proyectos comunitarios*",
                    descripción: "Te gusta la acción pero también sabés tomarte las cosas con calma. Te recomendamos acompañar a las comunidades en la ejecución de proyectos comunitarios de infraestructura, medioamblientales y/o de acceso a servicios codo a codo con vecinos y vecinas.",
                    valor: 0
                },
            ],
            formGroup: [
                {
                    title: "Hábitos",
                    pregunta: "¿Qué tomas por la mañana?",
                    value: "",
                    respuestas: [
                        { text: "Mate", value: "Mate", relate_to: "1" },
                        { text: "Café", value: "Café", relate_to: "2" },
                        { text: "Té", value: "Té", relate_to: "3" },]
                },
                {
                    title: "Habitos",
                    pregunta: "¿Que preferis?",
                    value: "",
                    respuestas: [
                        { text: "Mirar una Serie", value: "Mirar una Serie", relate_to: "1" },
                        { text: "Leer un Libro", value: "Leer un libro", relate_to: "2" },
                        { text: "Mirar una peli", value: "Mirar una peli", relate_to: "3" },]
                },
                {
                    title: "Habitos",
                    pregunta: "¿Cuál dirias que es tu hobbie preferido?",
                    value: "",
                    respuestas: [
                        { text: "Tomar algo con amistades", value: "Tomar algo con amistades", relate_to: "1" },
                        { text: "Hacer Ejercicio", value: "Hacer Ejercicio", relate_to: "2" },
                        { text: "Leer", value: "Leer", relate_to: "3" },]
                },
            ]
        }
    },

    methods: {
        nextStep(e) {
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
            this.nextStep();

        }
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