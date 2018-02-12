<template>
	<div id="application" :class="['set_'+$store.state.route.controller]">

		<!-- header -->
		<a v-if="$store.state.view.header" :class="{active: sidebar === 'menu'}" class="menu-toggle d-sm-none" v-on:click.prevent="sidebarToggle('menu')">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>

		<div v-if="$store.state.view.header" :class="{active: sidebar === 'menu'}" class="menu-sidebar d-sm-none">
			<sidebar-menu></sidebar-menu>
		</div>

		<application-header v-if="$store.state.view.header"></application-header>
		<application-header-mobile-icons v-if="$store.state.view.header"></application-header-mobile-icons>
		<!-- end header -->

		<div class="game_content">
			<!-- menu -->
			<main-menu v-if="$store.state.view.menu"></main-menu>
			<!-- end menu -->

			<!-- planets -->
			<a v-if="$store.state.view.planets" :class="{active: sidebar === 'planet'}" class="planet-toggle d-sm-none" v-on:click.prevent="sidebarToggle('planet')"><span>
					<span class="first"></span>
					<span class="second"></span>
					<span class="third"></span>
				</span>
			</a>

			<application-planets-list :class="{active: sidebar === 'planet'}" v-if="$store.state.view.planets"></application-planets-list>
			<!-- end planets -->

			<div class="main-content">
				<!-- planet panel -->
				<planet-panel v-if="$store.state.view.resources" :planet="$store.state.resources"></planet-panel>
				<!-- end planet panel -->

				<!-- messages -->
				<div v-if="$store.state.messages" v-for="item in $store.state.messages">
					<application-messages-row :item="item"></application-messages-row>
				</div>
				<!-- end messages -->

				<div class="main-content-row">
					<div v-if="html.length" ref="html"></div>
					<router-view v-if="$store.state.page"></router-view>
				</div>
			</div>
		</div>

		<!-- footer -->
		<application-footer v-if="$store.state.view.header"></application-footer>
		<!-- end footer -->

		<div id="ajaxLoader" :class="{active: $root.loader}"></div>
	</div>
</template>

<script>

	export default {
		name: "application",
		components: {
			'sidebar-menu': require('./views/app/sidebar-menu.vue'),
			'main-menu': require('./views/app/main-menu.vue'),
			'application-header': require('./views/app/header.vue'),
			'application-header-mobile-icons': require('./views/app/header-mobile-icons.vue'),
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