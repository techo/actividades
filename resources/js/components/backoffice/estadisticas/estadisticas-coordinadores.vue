<template>
	<div class="box">
		<div class="box-header">
			<estadisticas-filtros :value="filtros" @input="filtros = arguments[0]; filtrar()" ></estadisticas-filtros>
		</div>
		<div class="box-body" >
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li :class="{'active': tabs.coordinadores}" >
						<a 
							href="#coordinadores" 
							data-toggle="tab" 
							@click.prevent="tab('coordinadores')"
						>
						Coordinadores
						</a>
					</li>
				</ul>
			</div>

			<div class="tab-content" style="min-height: 500px">
				<div id="coordinadores" class="tab-pane" :class="{'active': tabs.coordinadores}" >
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
				coordinadores: true,
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