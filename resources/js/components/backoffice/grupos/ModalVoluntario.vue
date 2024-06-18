<template>
    <div class="modal fade" tabindex="-1" role="dialog" id="myModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ $t('backend.personal_data') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ $t('backend.name') }}</strong></p>
                            <p>{{ info.nombres}}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ $t('backend.last_name') }}</strong></p>
                            <p>{{ info.apellido }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ $t('backend.mobile_phone') }}</strong></p>
                            <p>{{ info.telefonoMovil}}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ $t('backend.email') }}:</strong></p>
                            <p>{{ info.mail}}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>{{ $t('backend.birthdate') }}:</strong></p>
                            <p>{{ info.fechaNacimiento }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>{{ $t('backend.gender') }}</strong></p>
                            <p><i
                                    class="fa"
                                    :class="{'fa-male': info.genero === 'M','fa-female': info.genero === 'F', 'fa-question': info.genero !== 'M' && info.sex !== 'F'}"
                            ></i></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            {{ info.nombre}}
                        </div>
                        <div class="col-md-6">

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ $t('backend.close') }}</button>
                    <!--<button type="button" class="btn btn-primary">Save changes</button>-->
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal --></template>

<script>
    export default {
        name: "modalVoluntario",
        props: {
            voluntario: {
                type: Object,
                default: {}
            }
        },
        data: function () {
            return {
                info: {}
            }
        },
        created: function() {
            Event.$on('vuetable-showModalVoluntario', this.verVoluntario);
        },
        methods: {
            verVoluntario(data) {
                this.info = data;
                $('#myModal').modal('show')
            }
        },
        watch: {
            voluntario (nuevo, viejo) {
                this.info = nuevo;
            }
        },
        filters: {
        }
    }
</script>

<style scoped>

</style>