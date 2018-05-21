<template>
	<div v-html="parsed"></div>
</template>

<script>
	export default {
		name: "text-viewer",
		props: ['text'],
		computed: {
			parsed () {
				return this.parser.parse(this.text);
			}
		}
	}
</script>