<template>
    <div class="btn-group">
        <a class="btn btn-default btn-rounded calendar-picker"
           data-toggle="collapse"
           aria-expand="true"
        >
            <span class="date-range-label">{{ dateRange }}</span>
            <span class="caret"></span>
        </a>
    </div>
</template>

<script>
    import moment from 'moment';

    export default {
        name: "daterangepicker",
        props: ['fechas', 'minDate', 'opens', 'maxDate', 'input', 'drops','edit'],
        data: function () {
            return {
                start: this.fechas.inicio,
                end: this.fechas.fin,
                showRanges: false,
                autoApply: false,
                eventName: 'apply' + this.input
            };
        },
        computed: {
            dateRange: function () {
                var start = moment(this.fechas.inicio);
                var end = moment(this.fechas.fin);

                if (start.format('MM-DD-YYYY') === end.format('MM-DD-YYYY')) {
                    return start.format('DD/MM/YYYY (HH:mm') + ' - ' + end.format('HH:mm)');
                }

                return start.format('DD/MM/YYYY HH:mm') + ' - ' + end.format('DD/MM/YYYY HH:mm');
            }
        },
        mounted: function () {
            var vm = this;
            this.fechas.inicio = moment(this.fechas.inicio);
            this.fechas.fin = moment(this.fechas.fin);
            this.$nextTick(function () {
                var options = {
                    locale: {
                        format: "DD/MM/YYYY",
                        separator: " - ",
                        applyLabel: "Aplicar",
                        cancelLabel: "Cancelar",
                        fromLabel: "Desde",
                        toLabel: "Hasta",
                        customRangeLabel: "Actividad",
                        weekLabel: "S",
                        daysOfWeek: [
                            "Do",
                            "Lu",
                            "Ma",
                            "Mi",
                            "Ju",
                            "Vi",
                            "Sa"
                        ],
                        monthNames: [
                            "Enero",
                            "Febrero",
                            "Marzo",
                            "Abril",
                            "Mayo",
                            "Junio",
                            "Julio",
                            "Agosto",
                            "Septiembre",
                            "Octubre",
                            "Noviembre",
                            "Diciembre"
                        ],
                        "firstDay": 1
                    },
                    timePicker: true,
                    opens: this.opens,
                    drops: this.drops,
                    startDate: this.fechas.inicio,
                    endDate: this.fechas.fin,
                    autoApply: this.autoApply,
                    alwaysShowCalendars: true,
                    disabled: this.edit,
                    editable: !this.edit
                };

                if (this.minDate) {
                    options.minDate = this.minDate;
                }
                if (this.maxDate) {
                    options.maxDate = this.maxDate;
                }

                if (this.showRanges) {
                    options.ranges = {
                        Today: [moment(), moment()],
                        Yesterday: [
                            moment().subtract(1, 'days'),
                            moment().subtract(1, 'days')
                        ],
                        'Last 7 Days': [
                            moment().subtract(6, 'days'),
                            moment()
                        ],
                        'Last 30 Days': [
                            moment().subtract(30, 'days'),
                            moment()
                        ],
                        'This Month': [
                            moment().startOf('month'),
                            moment().endOf('month')
                        ],
                        'Last Month': [
                            moment().subtract(1, 'month').startOf('month'),
                            moment().subtract(1, 'month').endOf('month')
                        ]
                    };
                }
                window.$(this.$el)
                    .daterangepicker(options)
                    .on('apply.daterangepicker', function (e, picker) {
                        vm.$emit(vm.eventName, picker.startDate, picker.endDate);
                        vm.start = picker.startDate;
                        vm.end = picker.endDate;
                    });
            });
        }
    };
</script>

<style scoped>

</style>