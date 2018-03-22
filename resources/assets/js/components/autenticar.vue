<template>
</template>

<script>
    import axios from 'axios';
    export default {
        name: "autenticar",
        mounted: function (argument) {
          this.mostrarLogin();
        },
        methods: {
          mostrarLogin: function () {
            $('#btnShowModal').trigger('click')
          }
        }
    }
</script>