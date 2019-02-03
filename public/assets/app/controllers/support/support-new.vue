<template>
	<div class="block page-support-new">
		<div class="title text-center">
			Новый запрос
		</div>
		<div class="content border-0">
			<div class="table">
				<div class="row">
					<div class="col-3 th">Тема:</div>
					<div class="col-9 th">
						<input type="text" v-model="subject" class="width-full" name="subject">
					</div>
				</div>
				<div class="row">
					<div class="col-3 th">Текст сообщения:</div>
					<div class="col-9 th">
						<text-editor v-model="text"></text-editor>
					</div>
				</div>
				<div class="row">
					<div class="col c">
						<input type="button" value="Отправить" @click="request">
						<input type="button" value="Закрыть" @click="$emit('close')">
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import { $post } from 'api'

	export default {
		name: "support-new",
		data () {
			return {
				text: '',
				subject: ''
			}
		},
		methods: {
			request ()
			{
				$post('/support/add/', {
					subject: this.subject,
					text: this.text
				})
				.then((result) =>
				{
					if (result.error)
					{
						$.alert({
							title: result.error.title,
							content: result.error.message
						});

						if (result.error.type === 'success')
						{
							this.$router.replace(this.$route.fullPath);
							this.$emit('close');
						}
					}
					else
					{
						this.$store.commit('PAGE_LOAD', result);
						this.$router.replace(this.$route.fullPath);
					}
				})
			}
		}
	}
</script>