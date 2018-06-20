<template>
	<table class="table">
		<tr v-if="data['title'] && data['title'].length">
			<td class="c error" v-html="data['title']"></td>
		</tr>
		<tr>
			<th class="errormessage" v-html="data['message']"></th>
		</tr>
	</table>
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
					this.$root.load(this.data['redirect'])
				}, this.data['timeout'] * 1000);
			}
		},
		destroyed () {
			clearTimeout(this.timeout);
		}
	}
</script>