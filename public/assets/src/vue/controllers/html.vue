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
					this.destroy();

				if (this.html && this.html.length > 0)
				{
					setTimeout(() => {
						this.evalJs(this.html);
					}, 25);

					this.component = new (Vue.extend({
						name: 'html-render-component',
						parent: this,
						template: '<div>'+this.html.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, '')+'</div>'
					}))().$mount();

					this.$nextTick(() => {
						this.$refs['component'].innerHTML = ''
						this.$refs['component'].appendChild(this.component.$el)
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
			destroy ()
			{
				this.component.$destroy();
				this.component = null

				if (this.$refs['component'])
					this.$refs['component'].innerHTML = ''
			}
		},
		beforeDestroy ()
		{
			if (this.component)
				this.destroy();
		}
	}
</script>