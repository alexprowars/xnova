<template>
	<div class="page-messages-write">
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
	import { required } from 'vuelidate/lib/validators'

	export default {
		name: "messages-write",
		props: {
			popup: {
				type: Object
			}
		},
		async asyncData ({ store })
		{
			const data = await store.dispatch('loadPage')

			return {
				data: data.page
			}
		},
		watchQuery: true,
		middleware: 'auth',
		data () {
			return {
				page: {},
				text: '',
				error: false
			}
		},
		validations: {
			text: {
				required
			},
		},
		created () {
			this.page = this.popup !== undefined ? this.popup : this.data
		},
		methods: {
			send ()
			{
				this.$v.$touch();

				if (!this.$v.$invalid)
				{
					this.$post('/messages/write/'+this.page['id']+'/', {
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
		},
		mounted () {
			this.text = this.page.text;
		},
	}
</script>