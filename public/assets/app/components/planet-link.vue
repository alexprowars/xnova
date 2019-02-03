<template>
	<router-link :to="'/galaxy/'+galaxy+'/'+system+'/'+planet+'/'">
		[{{ galaxy }}:{{ system }}:{{ planet }}]
	</router-link>
</template>

<script>
	export default {
		name: "planet-link",
		props: {
			galaxy: {
				type: Number,
				default: 1
			},
			system: {
				type: Number,
				default: 1
			},
			planet: {
				type: Number,
				default: 1
			}
		}
	}
</script>