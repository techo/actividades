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
				<div class="col-md-2">
					<label>{{ $t('backend.year') }}</label>
					<input type="number" class="form-control" v-model.number="anio" @change="cargar()">
				</div>
				<div class="col-md-3">
					<label>{{ $t('backend.granularity') }}</label>
					<select class="form-control" v-model="granularidad" @change="cargar()">
						<option value="mensual">{{ $t('backend.granularity_monthly') }}</option>
						<option value="trimestral">{{ $t('backend.granularity_quarterly') }}</option>
						<option value="semestral">{{ $t('backend.granularity_biannual') }}</option>
						<option value="anual">{{ $t('backend.granularity_annual') }}</option>
					</select>
				</div>
			</div>
		</div>

		<div class="box-body">
			<p v-if="!idPais" class="text-muted">
				{{ $t('backend.select_country_first') }}
			</p>

			<div v-if="idPais" class="table-responsive">
				<table class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="min-width: 240px">{{ $t('backend.indicator') }}</th>
							<th v-for="col in periodos" :key='"h" + (col.periodo === null ? "A" : col.periodo)'
								class="text-center" style="min-width: 92px">
								{{ col.etiqueta }}
								<i v-if="!col.editable" class="fa fa-lock text-muted"
									:title="$t('backend.period_closed')"></i>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="row in indicadores" :key="row.key">
							<td>
								{{ row.nombre }}
								<i v-if="row.nota" class="fa fa-info-circle text-yellow" :title="row.nota"></i>
							</td>
							<td v-for="cell in row.celdas" :key='row.key + "|" + (cell.periodo === null ? "A" : cell.periodo)'
								class="text-center">
								<input v-if="cell.editable" type="number" min="0"
									class="form-control input-sm text-center"
									v-model.number="edicion[celda(row.key, cell.periodo)]"
									@change="guardar(row, cell)">
								<span v-else>{{ cell.plan !== null ? cell.plan : '—' }}</span>

								<div class="small">
									<span class="text-muted">{{ $t('backend.real') }}:</span>
									{{ cell.real !== null ? cell.real : '—' }}
									<span v-if="cell.desempeno !== null" :class="claseDesempeno(cell.desempeno)">
										{{ cell.desempeno }}%
									</span>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</template>

<script>
import Alert from '../../plugins/Alert';

/**
 * Pantalla de Indicadores (Plan vs. Real) como matriz del año.
 *
 * El selector de granularidad (mensual / trimestral / semestral / anual) cambia
 * las columnas: cada columna es un período del año. El "Plan" se edita solo en
 * los períodos que todavía no cerraron (los cerrados van bloqueados); el "Real"
 * se agrega para coincidir con la granularidad (lo resuelve el backend con la
 * misma capa que la API de Power BI). Guardado por celda, versionado server-side.
 *
 * Acceso restringido a role:admin en la ruta — sin lógica de permisos propia acá.
 */
export default {
	components: { 'alert': Alert },
	data() {
		return {
			loading: false,
			paises: [],
			idPais: null,
			anio: moment().format('YYYY') * 1,
			granularidad: 'trimestral',
			periodos: [],
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
		celda(key, periodo) {
			return key + '|' + (periodo === null ? 'A' : periodo);
		},
		cargar() {
			this.loading = true;
			const params = { anio: this.anio, granularidad: this.granularidad };
			if (this.idPais) params.idPais = this.idPais;

			axios.get('/admin/ajax/estadisticas/indicadores', { params })
				.then((data) => {
					if (!this.idPais && data.data.idPais) {
						this.idPais = data.data.idPais;
					}
					this.periodos = data.data.periodos;
					this.indicadores = data.data.indicadores;

					const edicion = {};
					this.indicadores.forEach((row) => {
						row.celdas.forEach((cell) => {
							edicion[this.celda(row.key, cell.periodo)] = cell.plan;
						});
					});
					this.edicion = edicion;
					this.loading = false;
				})
				.catch(() => { this.loading = false; });
		},
		guardar(row, cell) {
			const valor = this.edicion[this.celda(row.key, cell.periodo)];
			if (valor === null || valor === undefined || valor === '') return;

			this.loading = true;
			axios.post('/admin/ajax/estadisticas/indicadores', {
				metric_key: row.key,
				idPais: this.idPais,
				anio: this.anio,
				granularidad: this.granularidad,
				periodo: cell.periodo,
				valor_planificado: valor,
			}).then((resp) => {
				cell.plan = parseFloat(resp.data.valor_planificado);
				cell.desempeno = (cell.plan > 0 && cell.real !== null)
					? Math.round(cell.real * 100 / cell.plan)
					: null;
				this.loading = false;
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
