<template>
	<div class="table">
		<div class="row" v-if="data['title'] && data['title'].length">
			<div class="col-12 c error" v-html="data['title']"></div>
		</div>
		<div class="row">
			<div class="col-12 th error-message" v-html="data['message']"></div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "error-message",
		props: {
			data: Object
		},
		data () {
			return {
				timeout: null
			}
		},
		mounted ()
		{
			if (this.data['timeout'] > 0 && this.data['redirect'])
			{
				this.timeout = setTimeout(() => {
					this.$router.push(this.data['redirect'])
				}, this.data['timeout'] * 1000);
			}
		},
		destroyed () {
			clearTimeout(this.timeout);
		}
	}
</script>