<template>
	<div id="application" :class="['set_'+$store.state.route.controller]">

		<application-header v-if="$store.state.view.header"></application-header>

		<main>
			<main-menu v-if="$store.state.view.menu" :active="sidebar === 'menu'"></main-menu>

			<application-planets-list v-if="$store.state.view.planets" :active="sidebar === 'planet'"></application-planets-list>

			<div class="main-content">
				<planet-panel v-if="$store.state.view.resources" :planet="$store.state.resources"></planet-panel>

				<div v-if="$store.state.messages" v-for="item in $store.state.messages">
					<application-messages-row :item="item"></application-messages-row>
				</div>

				<div class="main-content-row">
					<div v-if="html.length" ref="html"></div>
					<router-view v-if="$store.state.page"></router-view>
				</div>
			</div>
		</main>

		<application-footer v-if="$store.state.view.header"></application-footer>

		<div id="ajaxLoader" :class="{active: $root.loader}"></div>
	</div>
</template>

<script>

	export default {
		name: "application",
		components: {
			'main-menu': require('./views/app/main-menu.vue'),
			'application-header': require('./views/app/header.vue'),
			'application-footer': require('./views/app/footer.vue'),
			'application-planets-list': require('./views/app/planets-list.vue'),
			'planet-panel': require('./views/app/planet-panel.vue'),
			'application-messages-row': require('./views/app/messages-row.vue'),
		},
		computed: {
			html () {
				return this.$store.state.html;
			}
		},
		data: function ()
		{
			return {
				sidebar: '',
				html_component: null
			}
		},
		watch: {
		    '$route' () {
				this.sidebar = '';
		    },
			html (val) {
		    	this.renderHtml(val);
			}
		},
		methods: {
			sidebarToggle (type)
			{
				if (this.sidebar === type)
					this.sidebar = '';
				else
					this.sidebar = type;
			},
			renderHtml (html)
			{
				if (this.html_component !== null)
					this.html_component.$destroy();

				if (html.length > 0)
				{
					setTimeout(() => {
						this.$root.evalJs(html);
					}, 25);

					this.html_component = new (Vue.extend({
						name: 'html-render',
						template: '<div>'+html.replace(/<script[^>]*>(?:(?!<\/script>)[^])*<\/script>/g, '')+'</div>'
					}))().$mount();

					Vue.nextTick(() => {
						$(this.$refs['html']).html(this.html_component.$el);
					});
				}
			}
		},
		mounted ()
		{
			if (this.html.length)
				this.renderHtml(this.html);
		}
	}
</script>