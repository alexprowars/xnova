<template>
	<div id="application" v-bind:class="['set_'+$store.state.route.controller]">

		<!-- header -->
		<a v-if="$store.state.view.header && mobile" v-bind:class="{active: sidebar === 'menu'}" class="menu-toggle d-sm-none" v-on:click.prevent="sidebarToggle('menu')">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>

		<div v-if="$store.state.view.header && mobile" v-bind:class="{active: sidebar === 'menu'}" class="menu-sidebar d-sm-none">
			<sidebar-menu></sidebar-menu>
		</div>

		<application-header v-if="$store.state.view.header"></application-header>
		<application-header-mobile-icons v-if="$store.state.view.header && mobile"></application-header-mobile-icons>
		<!-- end header -->

		<div class="game_content">
			<!-- menu -->
			<main-menu v-if="$store.state.view.menu && !mobile"></main-menu>
			<!-- end menu -->

			<!-- planets -->
			<a v-if="$store.state.view.planets && mobile" v-bind:class="{active: sidebar === 'planet'}" class="planet-toggle d-sm-none" v-on:click.prevent="sidebarToggle('planet')"><span>
					<span class="first"></span>
					<span class="second"></span>
					<span class="third"></span>
				</span>
			</a>

			<application-planets-list v-bind:class="{active: sidebar === 'planet'}" v-if="$store.state.view.planets"></application-planets-list>
			<!-- end planets -->

			<div class="main-content">
				<!-- planet panel -->
				<planet-panel v-if="$store.state.view.resources" v-bind:planet="$store.state.resources"></planet-panel>
				<!-- end planet panel -->

				<!-- messages -->
				<div v-if="$store.state.messages" v-for="item in $store.state.messages">
					<application-messages-row v-bind:item="item"></application-messages-row>
				</div>
				<!-- end messages -->

				<div class="main-content-row">
					<router-view></router-view>
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
		data: function ()
		{
			return {
				mobile: false,
				sidebar: ''
			}
		},
		methods: {
			sidebarToggle: function (type)
			{
				if (this.sidebar === type)
					this.sidebar = '';
				else
					this.sidebar = type;
			},
			handleWindowResize: function (event) {
				this.mobile = (event.currentTarget.innerWidth < 480);
			}
		},
		created: function ()
		{
			if (window.innerWidth < 480)
				this.mobile = true;
		},
		mounted: function() {
			window.addEventListener('resize', this.handleWindowResize);
		},
		watch: {
		    '$route' () {
				this.sidebar = '';
		    }
		}
	}
</script>