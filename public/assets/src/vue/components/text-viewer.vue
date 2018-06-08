<template>
	<div v-html="parsed"></div>
</template>

<script>
	import {parser} from '../js/parser'

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