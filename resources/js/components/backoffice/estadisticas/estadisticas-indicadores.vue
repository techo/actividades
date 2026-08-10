<template>
	<div class="box">
		<alert :mostrar="loading"></alert>

		<div class="box-header">
			<div class="row">
				<div class="col-md-3">
					<label>{{ $t('backend.country') }}</label>
					<select class="form-control" v-model="idPais" @change="cargar()">
						<option v-for="p in paises" :value="p.id" :key="p.id">{{ p.nombre }}</option>
					</select>
				</div>
				<div class="col-md-3">
					<label>{{ $t('backend.year') }}</label>
					<input type="number" class="form-control" v-model.number="anio" @change="cargar()">
				</div>
				<div class="col-md-3">
					<label>{{ $t('backend.month') }}</label>
					<select class="form-control" v-model.number="mes" @change="cargar()">
						<option v-for="(nombreMes, i) in meses" :value="i + 1" :key="i">{{ nombreMes }}</option>
					</select>
				</div>
			</div>
		</div>

		<div class="box-body">
			<p v-if="!idPais" class="text-muted">
				{{ $t('backend.select_country_first') }}
			</p>

			<table class="table table-striped" v-if="idPais">
				<thead>
					<tr>
						<th>{{ $t('backend.indicator') }}</th>
						<th style="width: 150px">{{ $t('backend.planned') }}</th>
						<th style="width: 100px">{{ $t('backend.real') }}</th>
						<th style="width: 120px">{{ $t('backend.performance') }}</th>
						<th style="width: 90px"></th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="row in indicadores" :key="row.key">
						<td>
							{{ row.nombre }}
							<i v-if="row.nota" class="fa fa-info-circle text-yellow" :title="row.nota"></i>
						</td>
						<td>
							<input type="number" min="0" class="form-control input-sm" v-model.number="edicion[row.key]">
						</td>
						<td>{{ row.real !== null ? row.real : '—' }}</td>
						<td>
							<span v-if="row.desempeno !== null" :class="claseDesempeno(row.desempeno)">
								{{ row.desempeno }}%
							</span>
							<span v-else class="text-muted">{{ $t('backend.no_plan_defined') }}</span>
						</td>
						<td>
							<button class="btn btn-xs btn-primary" @click="guardar(row)">
								{{ $t('backend.save') }}
							</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
import Alert from '../../plugins/Alert';

/**
 * Prototipo de la pantalla de Indicadores (Plan vs. Real). Consume:
 *  - GET  /admin/ajax/estadisticas/indicadores  -> Real (MetricRegistry, sin
 *    tocar) + Plan (nuevo, PlanIndicador) por país/año/mes.
 *  - POST /admin/ajax/estadisticas/indicadores  -> guarda una nueva versión
 *    del Plan para un indicador puntual.
 *
 * Acceso ya restringido a role:admin en la ruta — este componente no agrega
 * ninguna lógica de permisos propia a propósito (ver plan de trabajo, Fase 0).
 */
export default {
	components: { 'alert': Alert },
	data() {
		return {
			loading: false,
			paises: [],
			idPais: null,
			anio: moment().format('YYYY') * 1,
			mes: moment().format('M') * 1,
			meses: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
			indicadores: [],
			edicion: {},
		};
	},
	mounted() {
		axios.get('/admin/ajax/paises').then((data) => {
			this.paises = data.data;
			this.cargar();
		});
	},
	methods: {
		cargar() {
			this.loading = true;
			const params = { anio: this.anio, mes: this.mes };
			if (this.idPais) params.idPais = this.idPais;

			axios.get('/admin/ajax/estadisticas/indicadores', { params })
				.then((data) => {
					if (!this.idPais && data.data.idPais) {
						this.idPais = data.data.idPais;
					}
					this.indicadores = data.data.indicadores;
					this.edicion = {};
					this.indicadores.forEach((row) => {
						this.edicion[row.key] = row.plan;
					});
					this.loading = false;
				})
				.catch(() => { this.loading = false; });
		},
		guardar(row) {
			const valor = this.edicion[row.key];
			if (valor === null || valor === undefined || valor === '') return;

			this.loading = true;
			axios.post('/admin/ajax/estadisticas/indicadores', {
				metric_key: row.key,
				idPais: this.idPais,
				anio: this.anio,
				mes: this.mes,
				valor_planificado: valor,
			}).then(() => {
				this.cargar();
			}).catch(() => { this.loading = false; });
		},
		claseDesempeno(valor) {
			if (valor >= 90) return 'label label-success';
			if (valor >= 60) return 'label label-warning';
			return 'label label-danger';
		},
	},
}
</script>
