<template>
    <span>
        <input type="text" v-bind:value="dataValor" class="dial-personas-eval">
    </span>
</template>

<script>
    export default {
        name: "knob",
        props: ['valor', 'simbolo', 'listener'],
        data() {
            return {
                dataValor: this.valor,
            }
        },
        created(){
            Event.$on(this.listener, this.actualizar);
        },
        mounted() {
            var vm = this;
            this.$nextTick(function () {
                window.$(this.$el).find('input').knob({
                    'min': 0,
                    'max': 100,
                    'readOnly': true,
                    'format': function (value) {
                        return value + vm.simbolo;
                    }
                });
            });
        },
        methods: {
            actualizar: function (valor) {
                 window.$(this.$el).find('input')
                     .val(valor)
                     .trigger('change');
            }
        }
    }
</script>

<style scoped>

</style>