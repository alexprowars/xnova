<template>
	<table class="table">
		<tr v-if="title.length">
			<td class="c error">{{ title }}</td>
		</tr>
		<tr>
			<th class="errormessage">{{ text }}</th>
		</tr>
	</table>
</template>

<script>
	export default {
		name: "message",
		props: ['title', 'text', 'time', 'redirect'],
		data () {
			return {
				timeout: null
			}
		},
		mounted ()
		{
			if (this.time > 0)
			{
				this.timeout = setTimeout(() => {
					load(this.redirect)
				}, this.time * 1000);
			}
		},
		destroyed () {
			clearTimeout(this.timeout);
		}
	}
</script>