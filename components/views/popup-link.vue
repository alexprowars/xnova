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
					return window.location.href = this.to.split('ajax').join('').split('popup').join('')

				this.$get(this.to, {
					popup: 'Y'
				})
				.then(async (result) =>
				{
					let component = this.$router.getMatchedComponents(this.to)

					if (component.length)
					{
						let comp = null

						if (typeof component[0] === 'object')
							comp = component[0]
						else
							comp = await component[0]()

						this.$modal.show(comp, {
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