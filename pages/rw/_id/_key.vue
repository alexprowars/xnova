<template>
	<div v-if="page" v-html="page['raport']"></div>
</template>

<script>
	export default {
		name: "rw",
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		layout: 'log',
		created ()
		{
			if (this.$store.state.user !== null && !this.page)
				this.$root.setLayout('default')
		}
	}
</script>