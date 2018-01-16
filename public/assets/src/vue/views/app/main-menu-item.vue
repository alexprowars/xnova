<script>
	export default {
		name: "main-menu-item",
		props: ['item'],
		render: function (createElement)
		{
			return createElement('li', {}, [
				createElement('a', {
					class: {
						active: this.$parent.active === this.item.id
					},
					attrs: {
						href: this.item.url,
						target: this.item.new === true ? '_blank' : ''
					}
				}, this.item.text)
			])
		}
	}
</script>