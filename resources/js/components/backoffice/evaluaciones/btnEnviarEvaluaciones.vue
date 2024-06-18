<template>
    <span>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalConfirmar">
            <i class="fa fa-paper-plane"></i> {{ $t('backend.send_evaluations') }}
        </button>

        <div class="modal fade" tabindex="-1" role="dialog" id="modalConfirmar">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            @click="success = false; error= false;">
                            <span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">{{ $t('backend.confirm_send_evaluations') }}</h4>
                    </div>
                    <div class="modal-body">
                        <span v-if="!error && !success">
                            <p>{{ $t('backend.on_click') }} <strong>{{ $t('backend.send') }}</strong> {{ $t('backend.email_all_enrolled') }}</p>
                            <p>
                                <b>{{ $t('backend.email_only_present') }}</b>
                            </p>
                            <p>{{ $t('backend.you_can_also') }} <strong>{{ $t('backend.copy_link') }}</strong> {{ $t('backend.evaluation_link_other_means') }}</p>
                            <input type="text" id="data-url-evaluaciones" tabindex="-1" aria-hidden="true" :value="urlEvaluaciones">
                        </span>
                        <span v-if="success">
                            <strong>
                                {{ $t('backend.notifications_sent_to') }} {{ notificados }} {{ $t('backend.enrolled') }}.
                            </strong>
                        </span>
                        <span v-if="error">
                            <p class="text-error">{{ $t('backend.error_sending_emails') }}</p>
                        </span>
                    </div>
                  <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal"
                            @click="success = false; error= false;">
                            <i class="fa fa-ban"></i>
                            {{ $t('backend.close') }}
                        </button>
                        <button v-bind:class="getClass(copiarClicked)"
                             type="button"
                             @click="copiarClipboard">
                            <i class="fa fa-clipboard"></i>
                            &nbsp {{ mensajeCopiar }}
                        </button>
                        <button type="button" class="btn btn-primary" @click="enviarEvaluaciones" v-if="!loading">
                            <i class="fa fa-paper-plane"></i>
                            {{ $t('backend.send') }}
                        </button>
                        <button type="button" class="btn btn-default" v-else>
                            <i class="fa fa-spinner fa-spin fa-fw"></i>
                            <span class="sr-only">Loading...</span> {{ $t('backend.wait') }}
                        </button>
                  </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->

    </span>
</template>

<script>

export default {
    name: "btnEnviarEvaluaciones",
    props: ['id'],
    data: function () {
        return {
            loading: false,
            success: false,
            error: false,
            actividad: {},
            mensajeCopiar: "Copiar link",
            copiarClicked: false,
            urlEvaluaciones: '',
            notificados: 0,
        }
    },
    created: function () {
        this.urlEvaluaciones = window.location.origin + '/actividades/' + this.id + '/evaluaciones';
    },
    methods: {
        enviarEvaluaciones: function () {
            this.loading = true;
            this.error = false;
            let url = '/admin/ajax/actividades/'+ this.id +'/enviar-evaluaciones';
            let payload = { actividad: this.id };
            axios.post(url, payload)
                .then(response => {
                    this.loading = false;
                    this.error = false;
                    this.success = true;
                    this.notificados = response.data;
                    this.readonly = true;
                })
                .catch((error) => {
                    this.loading = false;
                    this.error = true;
                });
        },
        getClass: function (clicked) {
            let btnClass = clicked ? 'btn-success' : 'btn-primary';
            return {
                'btn': true,
                [btnClass]: true
            };
        },
        copiarClipboard: function () {
            let url = document.getElementById('data-url-evaluaciones');
            url.select();
            document.execCommand("copy");
            this.copiarClicked = true;
            this.mensajeCopiar = "Copiado!";
        }
    }
}
</script>

<style scoped>
    .text-error {
        color: red;
    }
    #data-url-evaluaciones {
        position: absolute;
        left: -9999px;
    }
</style>