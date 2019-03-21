<template>
	<div id="report" class="table-responsive" v-html="page['raport']"></div>
</template>

<script>
	export default {
		name: "log-info",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		layout: 'empty',
	}
</script>