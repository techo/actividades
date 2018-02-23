Vue.config.devtools = true;

Vue.component('tarjeta', {
    template: '        <div class="card mb-4 box-shadow" >\n' +
    '            <div class="card-header">\n' +
    '                <h4 class="my-0 font-weight-normal">{{ actividad.nombreActividad }}</h4>\n' +
    '            </div>\n' +
    '            <div class="card-body">\n' +
    '                <h1 class="card-title pricing-card-title">$0 <small class="text-muted">/ mo</small></h1>\n' +
    '                <p class="list-unstyled mt-3 mb-4">{{ actividad.lugar }}</p>\n' +
    '                <button type="button" class="btn btn-lg btn-block btn-outline-primary">Quiero Participar</button>\n' +
    '            </div>\n' +
    '        </div>',
    props: ['actividad']

});

var app = new Vue({
    el: "#app",
    data: {
        actividades: []
    },
    mounted: function () {
        var self = this;
        $.ajax({
            url: '/ajax/actividades',
            method: 'GET',
            success: function (data) {
                console.log(data.data);
                self.actividades = data.data;
            },
            error: function (error) {
                console.log(error);
            }
        });
    }

});
