<template>
	<div v-if="page">
		<info-resources v-if="(page['i'] >= 1 && page['i'] <= 4) || page['i'] === 12 || page['i'] === 42 || (page['i'] >= 22 && page['i'] <= 24)" :data="page"></info-resources>
	</div>
</template>

<script>
	import InfoResources from './info-resources.vue'
	import router from 'router-mixin'

	export default {
		name: "info",
		mixins: [router],
		components: {
			InfoResources
		}
	}
</script>