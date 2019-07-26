<template>
	<div class="box">
		<div class="box-header">
			<estadisticas-filtros :value="filtros" @input="filtros = arguments[0]; filtrar()" ></estadisticas-filtros>
		</div>
		<div class="box-body" >
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li :class="{'active': display.inscripciones}" >
						<a href="#inscripciones" data-toggle="tab" @click.prevent="display.inscripciones = true; display.evaluaciones = false;">Inscripciones</a>
					</li>
					<li :class="{'active': display.evaluaciones} " >
						<a href="#evaluaciones" data-toggle="tab" @click.prevent="display.inscripciones = false; display.evaluaciones = true;">Evaluaciones</a>
					</li>
				</ul>
			</div>

			<div class="tab-content" style="min-height: 500px">
				<div id="inscripciones" class="tab-pane" :class="{'active': display.inscripciones}" >
					<div style="height: 200px" >
						<tabla-paginada ref="inscripciones" 
							apiUrl="/admin/ajax/estadisticas/inscripciones-por-actividad"
							:fields="[
								{'name': 'nombreActividad', 'sortField': 'nombreActividad', 'title': 'Actividad'},
				            	{'name': 'inscripciones', 'sortField': 'inscripciones', 'title': 'Inscripciones'},
				            	{'name': 'presentes', 'sortField': 'presentes', 'title': 'Presentes'},
				            ]"
				            :sortOrder="[{field: 'inscripciones', direction: 'desc'}]"
				            :moreParams="filtros"
				        ></tabla-paginada>
					</div>
				</div>
				<div id="evaluaciones" class="tab-pane" :class="{'active': display.evaluaciones}">
					<div style="height: 200px" >
						<tabla-paginada ref="evaluaciones" 
							apiUrl="/admin/ajax/estadisticas/evaluaciones-por-actividad"
							:fields="[
								{'name': 'nombreActividad', 'sortField': 'nombreActividad', 'title': 'Actividad'},
				            	{'name': 'puntaje', 'sortField': 'puntaje', 'title': 'Puntaje'},
				            	{'name': 'cantidad', 'sortField': 'cantidad', 'title': 'Cantidad'},
				            ]"
				            :sortOrder="[{field: 'puntaje', direction: 'asc'}]"
				            :moreParams="filtros"
				        ></tabla-paginada>
					</div>
				</div>
			</div>

		</div>
	</div>
</template>

<script>
import EstadisticasFiltros from './estadisticas-filtros';
import TablaPaginada from '../../plugins/TablaPaginada';

export default {
	components: { 
		'estadisticas-filtros': EstadisticasFiltros, 
		'tabla-paginada': TablaPaginada,
	},
	data() {
		return {
			loading: false,
			filtros: {},
			display: {
				inscripciones: true,
				evaluaciones: false,
			},
		};
	},
	mounted () {
		
	},
	computed: {
		inscripciones() {
			return this.display.inscripciones;
		}
	},
	methods: {
		filtrar () {
			this.$nextTick( () => { this.$refs.inscripciones.$refs.vuetable.refresh(); } )
			this.$nextTick( () => { this.$refs.evaluaciones.$refs.vuetable.refresh(); } )
		}
	},
}
</script>