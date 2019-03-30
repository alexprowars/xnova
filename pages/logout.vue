<template>
	<div></div>
</template>

<script>
	export default {
		name: 'logout',
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
	}
</script>