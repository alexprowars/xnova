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
		async asyncData ({ store })
		{
			const data = await store.dispatch('loadPage')

			return {
				data: data.page
			}
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