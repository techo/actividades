<template>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item"><a class="page-link" :href="meta.first_page_url ">&laquo; Primera</a></li>
            <li class="page-item"><a class="page-link" :href="meta.prev_page_url">Anterior</a></li>
            <li v-for="page in data" class="page-item"><a class="page-link" href="#">{{ page }}</a></li>
            <li class="page-item"><a class="page-link" :href="meta.next_page_url">Siguiente</a></li>
            <li class="page-item"><a class="page-link" :href="meta.last_page_url">Última &raquo;</a></li>
        </ul>
    </nav>

</template>

<script>
    export default {
        name: 'paginado',
        props: ['meta'],
    }
</script>