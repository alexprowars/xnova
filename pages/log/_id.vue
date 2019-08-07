<template>
	<div id="report" class="table-responsive" v-html="page['raport']"></div>
</template>

<script>
	export default {
		name: 'log-info',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		layout: 'empty',
	}
</script>