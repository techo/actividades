<template>
    <div>
        <section>
            <div class="card w-100 m-2" v-for="(equipo, index) in equipos">
                <cardEquiposPerfil 
                :equipo="equipo"
                :identifier="equipo.idEquipo"
                @saveCard="saveEquipo"
            />
            </div>
        </section>
    </div>
</template>

<script>
import _ from 'lodash'

export default {
    name: 'equipos',
    data: function () {
        var data = {
            guardo: false,
            formDirty: false,
            message: {
                danger: false,
                text: ''
            },
            creandoEquipo: false,
        }
        return data;
    },
    props: ['equipos', 'idPersona'],
    mounted: function () {
        this.formDirty = false
    },
    watch: {

    },
    methods: {

        saveEquipo: function (data) {
            this.guardo = false;
            
            this.creandoEquipo = false;
        },
    },
}
</script>
