<template>
	<simple-alert ref="loading"></simple-alert>
</template>
<script>
	import Simplert from 'vue2-simplert';

	export default {
		components: { 
			'simple-alert': Simplert 
		},
		props: { 
			'mostrar': { 
				type: Boolean, 
				default: false,
				},
		},
		watch: {
			mostrar: function (v) { 
				if(v) 
					this.abrir(); 
				else 
					this.cerrar(); 
			}
		},
		methods: {
			abrir() {
				this.$refs.loading.openSimplert({
					title: 'Espera...',
					message: "<i class=\"fa fa-spinner fa-spin fa-4x\"></i>",
					hideAllButton: true,
					isShown: true,
					disableOverlayClick: true,
					type: ''
				})
			},
			cerrar() {
				this.$refs.loading.justCloseSimplert();
			}
		}
	}
</script>