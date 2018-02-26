<template>
    <div class="card mb-4 box-shadow">
        <div class="card-header">
            <h4 class="my-0 font-weight-normal">{{ actividad.nombreActividad }}</h4>
        </div>
        <div class="card-body">
            <h1 class="card-title pricing-card-title">$0
                <small class="text-muted">/ mo</small>
            </h1>
            <p class="list-unstyled mt-3 mb-4">{{ actividad.lugar }}</p>
            <button type="button" class="btn btn-lg btn-block btn-outline-primary">Quiero Participar!</button>

        </div>
    </div>
</template>

<script>
    export default {
        name: 'tarjeta',
        props: ['actividad']
    }
</script>