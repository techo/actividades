<template>
	<div class="box">
		<alert :mostrar="loading"></alert>

		<div class="box-header with-border">
			<h3 class="box-title" v-if="idPais">
				{{ $t('backend.global_indicators') }}
				<small v-if="paisNombre">· {{ paisNombre }}</small>
			</h3>
			<div class="row" v-else>
				<div class="col-md-4">
					<label>{{ $t('backend.country') }}</label>
					<select class="form-control" v-model="idPais" @change="onPaisChange()">
						<option v-for="p in paises" :value="p.id" :key="p.id">{{ p.nombre }}</option>
					</select>
				</div>
			</div>
		</div>

		<div class="box-header" v-if="idPais">
			<div class="row">
				<div class="col-md-3">
					<label>{{ $t('backend.search') }}</label>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-search"></i></span>
						<input type="text" class="form-control" v-model="busqueda"
							:placeholder="$t('backend.search_indicator')">
					</div>
				</div>
				<div class="col-md-2">
					<label>{{ $t('backend.granularity') }}</label>
					<select class="form-control" v-model="granularidad" @change="cargar()">
						<option value="mensual">{{ $t('backend.granularity_monthly') }}</option>
						<option value="trimestral">{{ $t('backend.granularity_quarterly') }}</option>
						<option value="semestral">{{ $t('backend.granularity_biannual') }}</option>
						<option value="anual">{{ $t('backend.granularity_annual') }}</option>
					</select>
				</div>
				<div class="col-md-3">
					<label>{{ $t('backend.office') }}</label>
					<select class="form-control" v-model="idOficina" @change="cargar()">
						<option :value="null">{{ $t('backend.all_offices') }}</option>
						<option v-for="o in oficinas" :value="o.id" :key="o.id">{{ o.nombre }}</option>
					</select>
				</div>
				<div class="col-md-2">
					<label>{{ $t('backend.year') }}</label>
					<input type="number" class="form-control" v-model.number="anio" @change="cargar()">
				</div>
				<div class="col-md-2">
					<label>{{ $t('backend.indicator_type') }}</label>
					<select class="form-control" v-model="tipo">
						<option value="">{{ $t('backend.all_types') }}</option>
						<option v-for="g in gruposDisponibles" :value="g" :key="g">{{ g }}</option>
					</select>
				</div>
			</div>
		</div>

		<div class="box-body">
			<div v-if="idPais" class="table-responsive">
				<table class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="min-width: 240px">{{ $t('backend.indicator') }}</th>
							<th v-for="col in periodos" :key='"h" + col.anio + "-" + (col.periodo === null ? "A" : col.periodo)'
								class="text-center" style="min-width: 92px">
								{{ col.etiqueta }}
								<i v-if="!col.editable" class="fa fa-lock text-muted"
									:title="$t('backend.period_closed')"></i>
							</th>
						</tr>
					</thead>
					<tbody>
						<template v-for="grupo in filasAgrupadas">
							<tr class="active" :key="'grupo-' + grupo.nombre">
								<th :colspan="periodos.length + 1">{{ grupo.nombre }}</th>
							</tr>
							<tr v-for="row in grupo.items" :key="row.key">
								<td>
									{{ row.nombre }}
									<i v-if="row.nota" class="fa fa-info-circle text-yellow" :title="row.nota"></i>
								</td>
								<td v-for="cell in row.celdas" :key='row.key + "|" + cell.anio + "|" + (cell.periodo === null ? "A" : cell.periodo)'
									class="text-center">
									<input v-if="cell.editable" type="number" min="0"
										class="form-control input-sm text-center"
										v-model.number="edicion[celda(row.key, cell.anio, cell.periodo)]"
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
						</template>
						<tr v-if="filasAgrupadas.length === 0">
							<td :colspan="periodos.length + 1" class="text-muted text-center">
								{{ $t('backend.no_results') }}
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
 * Filtros: buscador por nombre (en vivo), granularidad (mensual/trimestral/
 * semestral/anual), oficina, año y tipo de indicador (grupo temático). El país
 * se resuelve del usuario y se muestra en el título; solo se ofrece un selector
 * de país como fallback si el usuario no tiene país asignado (admin global).
 *
 * Las filas se dividen por grupo (tipo de indicador). Cada columna es un período
 * (o un año, en la vista anual). El "Plan" se edita solo en períodos abiertos y
 * se guarda por celda, versionado server-side, con alcance país + oficina.
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
			paisNombre: null,
			oficinas: [],
			idOficina: null,
			anio: moment().format('YYYY') * 1,
			granularidad: 'trimestral',
			busqueda: '',
			tipo: '',
			periodos: [],
			indicadores: [],
			edicion: {},
		};
	},
	computed: {
		gruposDisponibles() {
			const vistos = [];
			this.indicadores.forEach((row) => {
				if (row.grupo && vistos.indexOf(row.grupo) === -1) vistos.push(row.grupo);
			});
			return vistos;
		},
		indicadoresFiltrados() {
			const q = this.busqueda.trim().toLowerCase();
			return this.indicadores.filter((row) => {
				const coincideTexto = !q || row.nombre.toLowerCase().indexOf(q) !== -1;
				const coincideTipo = !this.tipo || row.grupo === this.tipo;
				return coincideTexto && coincideTipo;
			});
		},
		filasAgrupadas() {
			const grupos = [];
			const indice = {};
			this.indicadoresFiltrados.forEach((row) => {
				const nombre = row.grupo || 'Otros';
				if (indice[nombre] === undefined) {
					indice[nombre] = grupos.length;
					grupos.push({ nombre: nombre, items: [] });
				}
				grupos[indice[nombre]].items.push(row);
			});
			return grupos;
		},
	},
	mounted() {
		axios.get('/admin/ajax/paises').then((data) => {
			this.paises = data.data;
			this.cargar();
		});
	},
	methods: {
		celda(key, anio, periodo) {
			return key + '|' + anio + '|' + (periodo === null ? 'A' : periodo);
		},
		onPaisChange() {
			this.oficinas = [];
			this.idOficina = null;
			this.cargar();
		},
		cargarOficinas() {
			if (!this.idPais) return;
			axios.get('/admin/ajax/oficinas/pais/' + this.idPais)
				.then((data) => { this.oficinas = data.data; })
				.catch(() => {});
		},
		cargar() {
			this.loading = true;
			const params = { anio: this.anio, granularidad: this.granularidad };
			if (this.idPais) params.idPais = this.idPais;
			if (this.idOficina) params.idOficina = this.idOficina;

			axios.get('/admin/ajax/estadisticas/indicadores', { params })
				.then((data) => {
					if (!this.idPais && data.data.idPais) {
						this.idPais = data.data.idPais;
					}
					this.paisNombre = data.data.paisNombre;
					this.periodos = data.data.periodos;
					this.indicadores = data.data.indicadores;

					const edicion = {};
					this.indicadores.forEach((row) => {
						row.celdas.forEach((cell) => {
							edicion[this.celda(row.key, cell.anio, cell.periodo)] = cell.plan;
						});
					});
					this.edicion = edicion;

					if (this.idPais && this.oficinas.length === 0) this.cargarOficinas();
					this.loading = false;
				})
				.catch(() => { this.loading = false; });
		},
		guardar(row, cell) {
			const valor = this.edicion[this.celda(row.key, cell.anio, cell.periodo)];
			if (valor === null || valor === undefined || valor === '') return;

			this.loading = true;
			axios.post('/admin/ajax/estadisticas/indicadores', {
				metric_key: row.key,
				idPais: this.idPais,
				idOficina: this.idOficina,
				anio: cell.anio,
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
