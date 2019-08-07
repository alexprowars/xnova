<template>
	<form ref="form" @submit.prevent="send" :method="method">
		<slot></slot>
	</form>
</template>

<script>
	export default {
		name: 'router-form',
		props: {
			method: {
				type: String,
				default: 'post'
			},
			action: {
				type: String,
				default: ''
			}
		},
		methods: {
			async send ()
			{
				let form = this.$refs['form']

				this.$store.commit('setLoadingStatus', true)

				let formData = new FormData(form);

				let action = this.action

				if (action.length === 0)
					action = this.$route.fullPath

				try
				{
					const result = await this.$post(action, formData)

					this.$store.commit('PAGE_LOAD', result)
					this.$store.commit('setLoadingStatus', false)
				}
				catch(e) {
					alert(e.message)
				}
			}
		},
	}
</script>