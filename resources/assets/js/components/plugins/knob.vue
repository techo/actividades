<template>
    <span>
        <input type="text" v-bind:value="dataValor" class="dial-personas-eval">
    </span>
</template>

<script>
    export default {
        name: "knob",
        props: ['valor', 'simbolo'],
        data() {
            return {
                dataValor: this.valor,
            }
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
        }
    }
</script>

<style scoped>

</style>