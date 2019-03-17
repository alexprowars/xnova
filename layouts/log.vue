<template>
	<div class="log-layout">
		<error-message v-if="error" :data="error"/>
		<Nuxt/>
	</div>
</template>

<script>
	export default {
		name: "",
		computed: {
			error () {
				return this.$store.state.error;
			},
		}
	}
</script>