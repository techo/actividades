<template>
	<div class="box">
		<div class="box-header">
			<estadisticas-filtros :value="filtros" @input="filtros = arguments[0]; filtrar()" ></estadisticas-filtros>
		</div>
		<div class="box-body" >
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li :class="{'active': tabs.inscripciones}" >
						<a href="#inscripciones" data-toggle="tab" @click.prevent="tab('inscripciones')">Inscripciones</a>
					</li>
					<li :class="{'active': tabs.evaluaciones} " >
						<a href="#evaluaciones" data-toggle="tab" @click.prevent="tab('evaluaciones')">Evaluaciones</a>
					</li>
				</ul>
			</div>

			<div class="tab-content" style="min-height: 500px">
				<div id="inscripciones" class="tab-pane" :class="{'active': tabs.inscripciones}" >
					<div style="height: 200px" >
						<tabla-paginada ref="inscripciones" 
							v-if="tabs.inscripciones"
							apiUrl="/admin/ajax/estadisticas/inscripciones-por-actividad"
							:fields="[
								{'name': 'nombreActividad', 'sortField': 'nombreActividad', 'title': 'Actividad'},
				            	{'name': 'inscripciones', 'sortField': 'inscripciones', 'title': 'Inscripciones'},
				            	{'name': 'presentes', 'sortField': 'presentes', 'title': 'Presentes'},
				            ]"
				            :sortOrder="[{field: 'inscripciones', direction: 'desc'}]"
				            :moreParams="filtros"
				            detailUrl="/admin/actividades/"
				        ></tabla-paginada>
					</div>
				</div>
				<div id="evaluaciones" class="tab-pane" :class="{'active': tabs.evaluaciones}">
					<div style="height: 200px" >
						<tabla-paginada ref="evaluaciones" 
							v-if="tabs.evaluaciones"
							apiUrl="/admin/ajax/estadisticas/evaluaciones-por-actividad"
							:fields="[
								{'name': 'nombreActividad', 'sortField': 'nombreActividad', 'title': 'Actividad'},
				            	{'name': 'puntaje', 'sortField': 'puntaje', 'title': 'Puntaje'},
				            	{'name': 'cantidad', 'sortField': 'cantidad', 'title': 'Cantidad'},
				            ]"
				            :sortOrder="[{field: 'puntaje', direction: 'asc'}]"
				            :moreParams="filtros"
				            detailUrl="/admin/actividades/"
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
			tabs: {
				inscripciones: true,
				evaluaciones: false,
			},
		};
	},
	mounted () {
		
	},
	computed: {
		inscripciones() {
			return this.tabs.inscripciones;
		}
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