<template>
	<div v-if="page" class="page-messages-write">
		<div v-if="error" v-html="error.message" :class="[error.type]" class="message"></div>
		<form action="" method="post" @submit.prevent="send">
			<div class="block">
				<div class="title">
					Отправка сообщения
				</div>
				<div class="content border-0">
					<div class="table form-group">
						<div class="row">
							<div class="col th">
								Получатель
							</div>
						</div>
						<div class="row">
							<div class="col c" v-html="page['to']"></div>
						</div>
						<div class="row">
							<div class="col th p-a-0">
								<text-editor :class="{error: $v.text.$error}" @change="$v.text.$touch()" v-model="text"></text-editor>
							</div>
						</div>
						<div class="row">
							<div class="col c">
								<input type="submit" value="Отправить">
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</template>

<script>
	import router from 'router-mixin'
	import { $post } from 'api'
	import {required} from 'vuelidate/lib/validators'

	export default {
		name: "messages-write",
		mixins: [router],
		data () {
			return {
				text: '',
				error: false
			}
		},
		validations: {
			text: {
				required
			},
		},
		methods: {
			afterLoad () {
				this.text = this.page.text;
			},
			send ()
			{
				this.$v.$touch();

				if (!this.$v.$invalid)
				{
					$post('/messages/write/'+this.page['id']+'/', {
						'text': this.text
					})
					.then((result) =>
					{
						if (result.redirect && result.redirect.length)
							window.location.href = result.redirect;
						else
						{
							this.error = result.error;

							if (this.error.type === 'success')
							{
								this.text = '';
								this.$v.$reset();
							}
						}
					})
				}
			}
		}
	}
</script>