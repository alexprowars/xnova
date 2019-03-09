<template>
	<div v-html="page.html"></div>
</template>

<script>
	export default {
		name: "html-content",
		props: {
			popup: {
				type: Object
			}
		},
		asyncData ({ store, route })
		{
			return store.dispatch('loadPage', route.fullPath).then ((data) =>
			{
				return {
					data: data.page
				}
			})
		},
		watchQuery: true,
		data () {
			return {
				page: {}
			}
		},
		created () {
			this.page = this.popup !== undefined ? this.popup : this.data
		},
	}
</script>