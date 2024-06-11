<template>
    <div class="modal-dialog">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h4 class="modal-title"> {{ $t('frontend.hello') }}!  </h4>
            </div>
            <div class="modal-body text-center">
                <p> 
                    {{ $t('frontend.use_regular_browser') }}
                </p>
            </div>
        </div>
    </div>
</template>


<script>


export default {
    props: [],
    data: function () {
        return {
        }
    },
    mounted() { 
        $('#aviso-modal').modal('show'); //sino pasan cosas raras con el scroll
    },
    watch: {
    },
    computed: {
    },
    methods: {
        cancelar() {
            this.hide();
        },
    }
}
</script>