<template>
    <div>
        <div class="form-group">
            <label>{{ $t('backend.actions') }}:</label>
            <div class="btn-group" role="group" aria-label="toolbar">
                <button type="button" class="btn btn-sm btn-default" :class="{'disabled': disabled}" @click="this.mostrarRolModal">{{ $t('backend.assign') }} {{ $t('backend.role') }}</button>
                <button type="button" class="btn btn-sm btn-default" :class="{'disabled': disabled}" @click="this.mostrarGrupoModal">{{ $t('backend.assign') }} {{ $t('backend.group') }}</button>
                <button type="button" class="btn btn-sm btn-default" :class="{'disabled': disabled}" @click="this.mostrarPuntoModal">{{ $t('backend.assign') }} {{ $t('backend.point') }}</button>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" :class="{'disabled': disabled}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ $t('backend.change_confirmation') }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a @click="cambiarConfirmacion(1, $event)">{{ $t('backend.confirmed') }}</a></li>
                        <li><a @click="cambiarConfirmacion(0, $event)">{{ $t('backend.unconfirmed') }}</a></li>
                    </ul>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" :class="{'disabled': disabled}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ $t('backend.change_payment') }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a @click="cambiarPago(1, $event)">{{ $t('backend.paid') }}</a></li>
                        <li><a @click="cambiarPago(0, $event)">{{ $t('backend.unpaid') }}</a></li>
                    </ul>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-default dropdown-toggle" :class="{'disabled': disabled}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        {{ $t('backend.change_attendance') }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a @click="cambiarAsistencia(1, $event)">{{ $t('backend.present') }}</a></li>
                        <li><a @click="cambiarAsistencia(0, $event)">{{ $t('backend.absent') }}</a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-sm btn-default" :class="{'disabled': disabled}" @click="mostrarDesinscribirModal($event)">{{ $t('backend.unsubscribe') }}</button>
                <inscripciones-rol-modal></inscripciones-rol-modal>
                <inscripciones-grupo-modal></inscripciones-grupo-modal>
                <inscripciones-punto-modal></inscripciones-punto-modal>
                <inscripciones-desinscribir-modal></inscripciones-desinscribir-modal>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "inscripciones-toolbar",
        data() {
           return {
                disabled: true
            }
        },
        created(){
            Event.$on('checkbox-toggled', this.toggleButtons);
        },
        methods: {
            mostrarRolModal: function () {
                if(this.disabled) return;
                Event.$emit('show-rol-modal');
            },
            mostrarGrupoModal: function () {
                if(this.disabled) return;
                Event.$emit('show-grupo-modal');
            },
            mostrarPuntoModal: function () {
                if(this.disabled) return;
                Event.$emit('show-punto-modal');
            },
            mostrarDesinscribirModal: function (event) {
                if(this.disabled) return;
                Event.$emit('show-desinscribir-modal');
            },
            cambiarConfirmacion: function (confirmacion, event) {
                Event.$emit('cambiar-confirmacion', confirmacion);
            },
            cambiarPago: function (pago, event) {
                Event.$emit('cambiar-pago', pago);
            },
            cambiarAsistencia: function (asistencia, event) {
                Event.$emit('cambiar-asistencia', asistencia);
            },
            toggleButtons: function (info) {
                this.disabled = (info.count === 0);
            }
        }
    }
</script>

<style scoped>

</style>