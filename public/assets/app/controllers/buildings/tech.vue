<template>
	<div v-if="page" class="page-building page-building-tech">
		<div class="block">
			<div class="title">Исследования</div>
			<div class="content page-building-items">
				<div class="row">
					<tech-row v-for="(item, i) in page.items" :key="i" :item="item"></tech-row>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import TechRow from './tech-row.vue'
	import router from 'router-mixin'

	export default {
		name: "tech",
		mixins: [router],
		components: {
			TechRow
		},
	}
</script>