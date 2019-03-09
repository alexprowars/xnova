<template>
	<div v-html="parsed"></div>
</template>

<script>
	import parser from '../../utils/parser.js'

	export default {
		name: "text-viewer",
		props: ['text'],
		computed: {
			parsed () {
				return parser.parse(this.text);
			}
		}
	}
</script>