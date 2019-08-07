<template>
	<div></div>
</template>

<script>
	export default {
		name: 'logout',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
	}
</script>