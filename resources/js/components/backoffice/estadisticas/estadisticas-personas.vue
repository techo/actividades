<template>
	<div class="box">
		<div class="box-header">
			<estadisticas-inscriptos-filtros :value="filtros" @input="filtros = arguments[0]; filtrar()" ></estadisticas-inscriptos-filtros>
		</div>
		<div class="box-body" >
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li :class="{'active': tabs.inscripciones} " >
						<a 
							href="#inscripciones" 
							data-toggle="tab" 
							@click.prevent="tab('inscripciones')"
						>
						{{ $t('backend.registrations') }}
						</a>
					</li>
					<!-- <li :class="{'active': tabs.evaluaciones_sociales} " >
						<a 
							href="#evaluaciones-sociales" 
							data-toggle="tab" 
							@click.prevent="tab('evaluaciones_sociales')"
						>
						{{ $t('backend.evaluations') }} {{ $t('backend.social') }}
						</a>
					</li>
					<li :class="{'active': tabs.evaluaciones_tecnicas} " >
						<a 
							href="#evaluaciones-tecnicas" 
							data-toggle="tab" 
							@click.prevent="tab('evaluaciones_tecnicas')"
						>
						{{ $t('backend.evaluations') }} {{ $t('backend.technical') }}
						</a>
					</li> -->
				</ul>
			</div>

			<div class="tab-content" style="min-height: 500px">
				<div id="inscripciones" class="tab-pane" :class="{'active': tabs.inscripciones}">
					<div style="height: 200px" >
						<tabla-paginada ref="inscripciones"
							v-if="tabs.inscripciones"
							apiUrl="/admin/ajax/estadisticas/inscripciones"
							:fields="[
								{'name': 'nombres', 'sortField': 'nombres', 'title': 'Nombre'},
				            	{'name': 'apellidoPaterno', 'sortField': 'apellidoPaterno', 'title': 'Apellido'},
				            	{'name': 'inscripciones', 'sortField': 'inscripciones', 'title': 'Inscripciones Totales'},
				            	{'name': 'presentes', 'sortField': 'presentes', 'title': 'Presentes Totales'},
				            ]"
				            :sortOrder="[{field: 'inscripciones', direction: 'desc'}]"
				            :moreParams="filtros"
				            detailUrl="/admin/usuarios/"
				        ></tabla-paginada>
					</div>
				</div>
				<div id="evaluaciones-sociales" class="tab-pane" :class="{'active': tabs.evaluaciones_sociales}">
					<div style="height: 200px" >
						<tabla-paginada ref="evaluaciones_sociales" 
							v-if="tabs.evaluaciones_sociales"
							apiUrl="/admin/ajax/estadisticas/evaluaciones-sociales"
							:fields="[
								{'name': 'nombres', 'sortField': 'nombres', 'title': 'Nombre'},
				            	{'name': 'apellidoPaterno', 'sortField': 'apellidoPaterno', 'title': 'Apellido'},
				            	{'name': 'puntaje', 'sortField': 'puntaje', 'title': 'Puntaje'},
				            	{'name': 'cantidad', 'sortField': 'cantidad', 'title': 'Cantidad'},
				            ]"
				            :sortOrder="[{field: 'puntaje', direction: 'asc'}]"
				            :moreParams="filtros"
				            detailUrl="/admin/usuarios/"
				        ></tabla-paginada>
					</div>
				</div>
				<div id="evaluaciones-tecnicas" class="tab-pane" :class="{'active': tabs.evaluaciones_tecnicas}">
					<div style="height: 200px" >
						<tabla-paginada ref="evaluaciones_tecnicas" 
							v-if="tabs.evaluaciones_tecnicas"
							apiUrl="/admin/ajax/estadisticas/evaluaciones-tecnicas"
							:fields="[
								{'name': 'nombres', 'sortField': 'nombres', 'title': 'Nombre'},
				            	{'name': 'apellidoPaterno', 'sortField': 'apellidoPaterno', 'title': 'Apellido'},
				            	{'name': 'puntaje', 'sortField': 'puntaje', 'title': 'Puntaje'},
				            	{'name': 'cantidad', 'sortField': 'cantidad', 'title': 'Cantidad'},
				            ]"
				            :sortOrder="[{field: 'puntaje', direction: 'asc'}]"
				            :moreParams="filtros"
				            detailUrl="/admin/usuarios/"
				        ></tabla-paginada>
					</div>
				</div>
			</div>

		</div>
	</div>
</template>

<script>
import EstadisticasFiltros from './estadisticas-filtros';
import EstadisticasInscriptosFiltros from './estadisticas-inscriptos-filtros';
import TablaPaginada from '../../plugins/TablaPaginada';

export default {
	components: { 
		'estadisticas-inscriptos-filtros': EstadisticasInscriptosFiltros, 
		'tabla-paginada': TablaPaginada,
	},
	data() {
		return {
			loading: false,
			filtros: {},
			tabs: {
				inscripciones: true,
				evaluaciones_sociales: false,
				evaluaciones_tecnicas: false,
			},
		};
	},
	methods: {
		filtrar () {
			let t = Object.keys(this.tabs)
			for (let i in t) {
				if(this.tabs[t[i]] == true) {
					this.$nextTick( () => { this.$refs[t[i]].$refs.vuetable.refresh(); } )
				}
			}
		},
		tab (nombre) {
			let t = Object.keys(this.tabs)
			for (let i in t) {
				if(t[i] == nombre) 
					this.tabs[t[i]] = true;
				else
					this.tabs[t[i]] = false;

			}
		},
	},
}
</script>