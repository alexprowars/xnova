<template>
	<form ref="form" @submit.prevent="send" :method="method">
		<slot></slot>
	</form>
</template>

<script>
	import { $post } from 'api'
	import app from 'app'

	export default {
		name: "router-form",
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
			send ()
			{
				let form = this.$refs['form']

				app.loader = true;

				let formData = new FormData(form);

				form.querySelectorAll('input[type=submit], button[type=submit]').forEach((item) =>
				{
					if (item.name && item.name.length > 0)
						formData.append(item.name, item.value)
				})

				let action = this.action

				if (action.length === 0)
					action = this.$route.fullPath

				$post(action, formData)
				.then((result) =>
				{
					app.$store.commit('PAGE_LOAD', result)
					app.$router.replace(result['url'])
				}, () => {
					alert('Что-то пошло не так!? Попробуйте еще раз');
				})
				.then(() => {
					app.loader = false;
				})
			}
		}
	}
</script>