<template>
	<a :href="to" @click.prevent="load" :title="title">
		<slot></slot>
	</a>
</template>

<script>
	export default {
		name: "popup-link",
		props: {
			to: String,
			width: {
				type: Number,
				default: 600
			},
			title: {
				type: String,
				default: ''
			},
		},
		methods: {
			load ()
			{
				if (this.$store.getters.isMobile)
					return window.location.href = url.split('ajax').join('').split('popup').join('');

				this.$get(this.to, {
					popup: 'Y'
				})
				.then(result =>
				{
					let component = this.$router.getMatchedComponents(this.to)

					if (component.length)
					{
						this.$modal.show(component[0], {
							popup: result['page']
						}, {
							width: this.width,
							height: 'auto'
						})
					}
				})
			}
		}
	}
</script>