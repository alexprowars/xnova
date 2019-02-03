<template>
	<div v-if="page" v-html="page.html"></div>
</template>

<script>
	import router from 'router-mixin'

	export default {
		name: "html-content",
		mixins: [router]
	}
</script>