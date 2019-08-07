<template>
	<div class="log-layout">
		<ErrorMessage v-if="error" :data="error"/>
		<Nuxt/>
	</div>
</template>

<script>
	import ErrorMessage from '~/components/views/message.vue'

	export default {
		components: {
			ErrorMessage,
		},
		computed: {
			error () {
				return this.$store.state.error || false;
			},
		}
	}
</script>