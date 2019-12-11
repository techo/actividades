<template>
    <div class="modal fade" tabindex="-1" role="dialog" id="myModal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Datos Personales</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre</strong></p>
                            <p>{{ info.nombres}}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Apellido</strong></p>
                            <p>{{ info.apellido }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Teléfono móvil</strong></p>
                            <p>{{ info.telefonoMovil}}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong></p>
                            <p>{{ info.mail}}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Fecha de nacimiento:</strong></p>
                            <p>{{ info.fechaNacimiento }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Género</strong></p>
                            <p><i
                                    class="fa"
                                    :class="{'fa-male': info.sexo === 'M','fa-female': info.sexo === 'F', 'fa-question': info.sexo !== 'M' && info.sex !== 'F'}"
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
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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