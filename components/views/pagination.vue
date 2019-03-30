<template>
	<nav>
		<ul class="pagination pagination-sm">
			<li v-for="item in items" class="page-item" :class="{active: options['page'] === item}">
				<a v-if="item > 0" href @click.prevent="load(item)" class="page-link">{{ item }}</a>
				<a v-else="" href @click.prevent="load(item)" class="page-link">...</a>
			</li>
		</ul>
	</nav>
</template>

<script>
	export default {
		name: "pagination",
		props: {
			options: {
				type: Object
			}
		},
		computed: {
			pages () {
				return Math.ceil(this.options['total'] / this.options['limit']);
			},
			items ()
			{
				let end = false;
				let arr = [];

				for (let i = 1; i <= this.pages; i++)
				{
					if ((this.options['page'] <= i + 3 && this.options['page'] >= i - 3) || i === 1 || i === this.pages || this.pages <= 6)
					{
						end = false;

						arr.push(i);
					}
					else
					{
						if (end === false)
							arr.push(0);

						end = true;
					}
				}

				return arr;
			}
		},
		methods: {
			load (page) {
				this.$router.push(this.$store.state.url+(this.$store.state.url.indexOf('?') >= 0 ? '&' : '?')+'p='+page);
			}
		}
	}
</script>