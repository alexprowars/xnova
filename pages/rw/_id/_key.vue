<template>
	<div v-if="page" v-html="page['raport']"></div>
</template>

<script>
	export default {
		name: 'rw',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		layout: 'empty',
		created ()
		{
			if (this.$store.state.user !== null && !this.page)
				this.$root.setLayout('default')
		}
	}
</script>