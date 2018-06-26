<template>
	<div ref="component"></div>
</template>

<script>
	import Vue from 'vue'
	import router from 'router-mixin'

	export default {
		name: "html-render",
		mixins: [router],
		computed: {
			html () {
				return this.$store.state.html;
			},
		},
		data () {
			return {
				component: null
			}
		},
		methods: {
			render () {
				if (this.component !== null)
					this.component.$destroy();

				if (this.html.length > 0)
				{
					setTimeout(() => {
						this.$root.evalJs(this.html);
					}, 25);

					this.component = new (Vue.extend({
						name: 'html-render-component',
						parent: this,
						template: '<div>'+this.html.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, '')+'</div>'
					}))().$mount();

					Vue.nextTick(() => {
						$(this.$refs['component']).html(this.component.$el);
					});
				}
			},
			afterLoad () {
				this.render()
			}
		},
		destroyed () {
			this.component.$destroy();
		}
	}
</script>