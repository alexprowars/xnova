<template>
	<div v-html="parsed"></div>
</template>

<script>
	import parser from '~/utils/parser'

	export default {
		name: "text-viewer",
		props: {
			text: {
				type: String,
				default: '',
			}
		},
		computed: {
			parsed () {
				return parser.parse(this.text);
			}
		}
	}
</script>