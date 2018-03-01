<template>
    <div class="row">
        <div class="col-md-3">
            <select name="actividad">
                <option value="1">Actividades en Asentamientos</option>
                <option value="2">Eventos Especiales</option>
                <option value="3">Actividades en Oficina</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="tipo">

                    <option value="@{{ tipo.idTipo }}">@{{ tipo.nombre}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="zona">
                <option value="1">Buenos Aires</option>
                <option value="2">Capital Federal</option>
                <option value="3">Otro</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary btn-large">Borra Filtros</button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "filtro",
        props: ['tipos']
    }
</script>