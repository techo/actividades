<template>
    <div class="comunicaciones-enviadas-component">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Comunicaciones enviadas</h3>
            </div>

            <div class="box-body">
                <div class="callout callout-info">
                    <p style="margin-bottom:0">
                        Historial de comunicaciones despachadas. La <strong>conversión</strong> cuenta a los
                        destinatarios que se inscribieron a la actividad (o se convirtieron, en campañas)
                        <strong>después</strong> de recibir la comunicación. Es un indicador de correlación,
                        no de causa directa.
                    </p>
                </div>

                <div v-if="cargando" class="text-center" style="padding:30px">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>

                <div v-else-if="items.length === 0" class="text-center text-muted" style="padding:30px">
                    Todavía no se envió ninguna comunicación.
                </div>

                <div v-else class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Canal</th>
                                <th>Objetivo</th>
                                <th>Audiencia</th>
                                <th>Países</th>
                                <th class="text-right">Destinatarios</th>
                                <th class="text-right">Conversión</th>
                                <th>Envió</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in items" :key="c.id">
                                <td style="white-space:nowrap">{{ c.fecha }}</td>
                                <td>
                                    <span class="label" :class="c.canal === 'email' ? 'label-primary' : 'label-info'">
                                        <i :class="c.canal === 'email' ? 'fa fa-envelope' : 'fa fa-bell'"></i>
                                        {{ c.canal }}
                                    </span>
                                </td>
                                <td>
                                    <i :class="c.objetivo_tipo === 'campaign' ? 'fa fa-bullhorn' : 'fa fa-calendar-check-o'"
                                       class="text-muted"></i>
                                    {{ c.objetivo_nombre }}
                                </td>
                                <td>{{ c.audiencia }}</td>
                                <td>{{ c.paises }}</td>
                                <td class="text-right">{{ c.destinatarios }}</td>
                                <td class="text-right">
                                    <template v-if="c.conversion !== null">
                                        <strong>{{ c.conversion }}</strong>
                                        <span class="text-muted">({{ c.conversion_pct }}%)</span>
                                        <br>
                                        <small class="text-muted">{{ c.conversion_label }}</small>
                                    </template>
                                    <span v-else class="text-muted" title="Sin atribución directa para este tipo">—</span>
                                </td>
                                <td>{{ c.admin || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box-footer clearfix" v-if="!cargando && lastPage > 1">
                <span class="text-muted">Página {{ currentPage }} de {{ lastPage }} · {{ total }} comunicación(es)</span>
                <div class="pull-right">
                    <button class="btn btn-default btn-sm" :disabled="currentPage <= 1" @click="irA(currentPage - 1)">
                        <i class="fa fa-angle-left"></i> Anterior
                    </button>
                    <button class="btn btn-default btn-sm" :disabled="currentPage >= lastPage" @click="irA(currentPage + 1)">
                        Siguiente <i class="fa fa-angle-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "comunicaciones-enviadas",
        data() {
            return {
                items: [],
                currentPage: 1,
                lastPage: 1,
                total: 0,
                cargando: false,
            }
        },
        created() {
            this.cargar(1);
        },
        methods: {
            cargar(page) {
                this.cargando = true;
                axios.get('/admin/ajax/comunicaciones/enviadas', { params: { page } })
                    .then((r) => {
                        this.items = r.data.data;
                        this.currentPage = r.data.current_page;
                        this.lastPage = r.data.last_page;
                        this.total = r.data.total;
                        this.cargando = false;
                    })
                    .catch(() => { this.cargando = false; });
            },
            irA(page) {
                if (page < 1 || page > this.lastPage) return;
                this.cargar(page);
            },
        }
    }
</script>
