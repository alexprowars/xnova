<template>
	<span :class="style">
		{{ value|number }}
	</span>
</template>

<script>
	export default {
		name: "colored",
		props: ['value'],
		computed: {
			style () {
				return (this.value > 0 ? 'positive' : (this.value < 0 ? 'negative' : ''))
			}
		}
	}
</script>