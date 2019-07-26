<template>
	<div class="box">
		<div class="box-header">
			<estadisticas-filtros :value="filtros" @input="filtros = arguments[0]; filtrar()" ></estadisticas-filtros>
		</div>
		<div class="box-body" >
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li :class="{'active': display.coordinadores}" >
						<a 
							href="#coordinadores" 
							data-toggle="tab" 
							@click.prevent="tab('coordinadores')"
						>
						Coordinadores
						</a>
					</li>
					<li :class="{'active': display.inscripciones} " >
						<a 
							href="#inscripciones" 
							data-toggle="tab" 
							@click.prevent="tab('inscripciones')"
						>
						Inscripciones
						</a>
					</li>
					<li :class="{'active': display.evaluaciones_sociales} " >
						<a 
							href="#evaluaciones-sociales" 
							data-toggle="tab" 
							@click.prevent="tab('evaluaciones_sociales')"
						>
						Evaluaciones Sociales
						</a>
					</li>
					<li :class="{'active': display.evaluaciones_tecnicas} " >
						<a 
							href="#evaluaciones-tecnicas" 
							data-toggle="tab" 
							@click.prevent="tab('evaluaciones_tecnicas')"
						>
						Evaluaciones Técnicas
						</a>
					</li>
				</ul>
			</div>

			<div class="tab-content" style="min-height: 500px">
				<div id="coordinadores" class="tab-pane" :class="{'active': display.coordinadores}" >
					<div style="height: 200px" >
						<tabla-paginada ref="coordinadores" 
							apiUrl="/admin/ajax/estadisticas/coordinadores"
							:fields="[
								{'name': 'nombres', 'sortField': 'nombres', 'title': 'Nombre'},
				            	{'name': 'apellidoPaterno', 'sortField': 'apellidoPaterno', 'title': 'Apellido'},
				            	{'name': 'inscripciones', 'sortField': 'inscripciones', 'title': 'Inscripciones'},
				            	{'name': 'presentes', 'sortField': 'presentes', 'title': 'Presentes'},
				            ]"
				            :sortOrder="[{field: 'inscripciones', direction: 'desc'}]"
				            :moreParams="filtros"
				        ></tabla-paginada>
					</div>
				</div>
				<div id="inscripciones" class="tab-pane" :class="{'active': display.inscripciones}">
					<div style="height: 200px" >
						<tabla-paginada ref="inscripciones" 
							apiUrl="/admin/ajax/estadisticas/inscripciones"
							:fields="[
								{'name': 'nombres', 'sortField': 'nombres', 'title': 'Nombre'},
				            	{'name': 'apellidoPaterno', 'sortField': 'apellidoPaterno', 'title': 'Apellido'},
				            	{'name': 'inscripciones', 'sortField': 'inscripciones', 'title': 'Inscripciones'},
				            	{'name': 'presentes', 'sortField': 'presentes', 'title': 'Presentes'},
				            ]"
				            :sortOrder="[{field: 'inscripciones', direction: 'desc'}]"
				            :moreParams="filtros"
				        ></tabla-paginada>
					</div>
				</div>
				<div id="evaluaciones-sociales" class="tab-pane" :class="{'active': display.evaluaciones_sociales}">
					<div style="height: 200px" >
						<tabla-paginada ref="evaluaciones_sociales" 
							apiUrl="/admin/ajax/estadisticas/evaluaciones-sociales"
							:fields="[
								{'name': 'nombres', 'sortField': 'nombres', 'title': 'Nombre'},
				            	{'name': 'apellidoPaterno', 'sortField': 'apellidoPaterno', 'title': 'Apellido'},
				            	{'name': 'puntaje', 'sortField': 'puntaje', 'title': 'Puntaje'},
				            	{'name': 'cantidad', 'sortField': 'cantidad', 'title': 'Cantidad'},
				            ]"
				            :sortOrder="[{field: 'puntaje', direction: 'asc'}]"
				            :moreParams="filtros"
				        ></tabla-paginada>
					</div>
				</div>
				<div id="evaluaciones-tecnicas" class="tab-pane" :class="{'active': display.evaluaciones_tecnicas}">
					<div style="height: 200px" >
						<tabla-paginada ref="evaluaciones_tecnicas" 
							apiUrl="/admin/ajax/estadisticas/evaluaciones-tecnicas"
							:fields="[
								{'name': 'nombres', 'sortField': 'nombres', 'title': 'Nombre'},
				            	{'name': 'apellidoPaterno', 'sortField': 'apellidoPaterno', 'title': 'Apellido'},
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
				coordinadores: true,
				inscripciones: false,
				evaluaciones_sociales: false,
				evaluaciones_tecnicas: false,
			},
		};
	},
	methods: {
		filtrar () {
			this.$nextTick( () => { this.$refs.coordinadores.$refs.vuetable.refresh(); } )
			this.$nextTick( () => { this.$refs.inscripciones.$refs.vuetable.refresh(); } )
			this.$nextTick( () => { this.$refs.evaluaciones_sociales.$refs.vuetable.refresh(); } )
			this.$nextTick( () => { this.$refs.evaluaciones_tecnicas.$refs.vuetable.refresh(); } )
		},
		tab (nombre) {
			for (let t in Object.keys(this.display)) {
				if(t == nombre)
					this.display[t] = true;
				else
					this.display[t] = false;
			}
		},
	},
}
</script>