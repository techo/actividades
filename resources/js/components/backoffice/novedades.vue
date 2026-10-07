<template>
    <div v-if="novedad" class="alert alert-info alert-dismissible" style="border-radius: 0px; margin-bottom: 0px;">
        <button type="button" class="close" aria-hidden="true" @click.prevent="cerrar">×</button>
        <i class="icon fa fa-info-circle"></i>
        {{ novedad.texto }}
        <template v-if="novedad.link">
            &nbsp;
            <a style="font-weight: bold;" :href="novedad.link" target="_blank" rel="noopener">Más info</a>
        </template>
    </div>
</template>

<script>
    // Barra rotativa: en cada carga del backoffice muestra una novedad activa distinta.
    // Primero las que este navegador todavía no vio (de la más nueva a la más vieja) y,
    // cuando ya vio todas, las va ciclando. La X descarta esa novedad para siempre en
    // este navegador. Todo en localStorage: si no está disponible, solo rota sin memoria.
    const KEY_VISTAS = 'novedades_vistas';
    const KEY_DESCARTADAS = 'novedades_descartadas';
    const KEY_ULTIMA = 'novedades_ultima';

    function leer(key, porDefecto) {
        try {
            const valor = JSON.parse(localStorage.getItem(key));
            return valor === null ? porDefecto : valor;
        } catch (e) { return porDefecto; }
    }

    function guardar(key, valor) {
        try { localStorage.setItem(key, JSON.stringify(valor)); } catch (e) {}
    }

    export default {
        name: "novedades",
        data: function () {
            return {
                novedad: null,
            }
        },
        mounted: function () {
            axios.get('/admin/novedades')
                .then((response) => { this.elegir(response.data || []); })
                .catch(() => {});
        },
        methods: {
            elegir: function (activas) {
                const ids = activas.map(n => n.id);
                // Se olvidan las que ya no están activas para que el storage no crezca.
                const vistas = leer(KEY_VISTAS, []).filter(id => ids.includes(id));
                const descartadas = leer(KEY_DESCARTADAS, []).filter(id => ids.includes(id));
                guardar(KEY_DESCARTADAS, descartadas);

                const pool = activas.filter(n => !descartadas.includes(n.id));
                if (!pool.length) return;

                let elegida = pool.find(n => !vistas.includes(n.id));
                if (!elegida) {
                    const i = pool.findIndex(n => n.id === leer(KEY_ULTIMA, null));
                    elegida = pool[(i + 1) % pool.length];
                }

                if (!vistas.includes(elegida.id)) vistas.push(elegida.id);
                guardar(KEY_VISTAS, vistas);
                guardar(KEY_ULTIMA, elegida.id);
                this.novedad = elegida;
            },
            cerrar: function () {
                const descartadas = leer(KEY_DESCARTADAS, []);
                descartadas.push(this.novedad.id);
                guardar(KEY_DESCARTADAS, descartadas);
                this.novedad = null;
            }
        }
    }
</script>
