<template>
	<div class="page-html" ref="component"></div>
</template>

<script>
	import Vue from 'vue'
	import router from 'router-mixin'
	import { addScript } from 'helpers'

	export default {
		name: "html-render",
		mixins: [router],
		computed: {
			html () {
				return this.page ? this.page.html : '';
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
						this.evalJs(this.html);
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
			},
			evalJs (html)
			{
				if (html.length > 0)
				{
					let j = $('<div/>').append(html)

					j.find("script").each(function()
					{
						if ($(this).attr('src') !== undefined)
							addScript($(this).attr('src'))
						else
							jQuery.globalEval($(this).text());
					});
				}
			},
		},
		destroyed ()
		{
			if (this.component)
				this.component.$destroy();
		}
	}
</script>