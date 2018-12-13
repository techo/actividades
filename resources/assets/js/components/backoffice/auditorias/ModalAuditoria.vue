<template>
    <div class="modal fade" tabindex="-1" role="dialog" id="modalAuditoria">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Auditoria del registro #{{ info.id }} tipo {{ info.tabla }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template v-for="registro in info.registros">
                                        <tr>
                                            <td>{{ registro.persona ? registro.persona.nombres + ' ' + registro.persona.apellidoPaterno : 'Anónimo' }}</td>
                                            <td>{{ registro.fecha_creacion }}</td>
                                            <td><a class="btn btn-small" @click="mostrar(registro)">Ver estado anterior</a></td>
                                        </tr>
                                        <tr v-if="registro.visible">
                                            <td colspan="3">
                                                <pre style="white-space:pre-wrap; overflow-wrap:break-word;">{{ JSON.stringify(JSON.parse(registro.informacion), null, 4).trim() }}</pre>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal --></template>

<script>
    export default {
        name: "modalAuditoria",
        props: ['auditoria'],
        data: function () {
            return {
                info: {},
            }
        },
        created: function() {
            Event.$on('cargarAuditoria', this.cargarAuditoria);
        },
        methods: {
            cargarAuditoria: function(params) {
                this.axiosGet(
                    '/ajax/auditorias/' + params.tabla + '/' + params.id,
                    function (response, self) {
                        response.registros.forEach(function(registro) {
                            registro.visible = false;
                        });
                        self.info = response;
                        $('#modalAuditoria').modal('show');
                    }
                );
            },
            mostrar: function(registro) {
                registro.visible = !registro.visible;
            }
        }
    }
</script>

<style scoped>

</style>