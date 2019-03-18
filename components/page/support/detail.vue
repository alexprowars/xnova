<template>
	<div class="block page-support-detail">
		<div class="title text-center">
			Текст обращения
		</div>
		<div class="content border-0">
			<div class="table">
				<div class="row">
					<div class="col th text-left" v-html="item['text']"></div>
				</div>
				<div v-if="item['status'] === 0" class="row">
					<div class="col c">Закрыт</div>
				</div>
				<div v-else>
					<div class="row">
						<div class="col c">Ответ</div>
					</div>
					<div class="row">
						<div class="col th">
							<text-editor v-model="text"></text-editor>
						</div>
					</div>
					<div class="row">
						<div class="col c">
							<input type="button" value="Ответить" @click="answer">
							<input type="button" value="Закрыть" @click="$emit('close')">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "support-detail",
		props: ['item'],
		data () {
			return {
				text: ''
			}
		},
		methods: {
			answer () {
				this.$post('/support/answer/'+this.item['id']+'/', {
					text: this.text
				})
				.then((result) =>
				{
					if (result.error && (result.error.type === 'error' || result.error.type === 'success'))
					{
						this.$dialog.alert({
							title: result.error.title,
							body: result.error.message,
						}, {
							okText: 'Закрыть'
						});

						if (result.error.type === 'success')
						{
							this.$router.replace('/support/');
							this.$emit('close');
						}
					}
					else
						this.$store.commit('PAGE_LOAD', result);
				})
			}
		}
	}
</script>