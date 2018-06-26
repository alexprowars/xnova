<template>
	<div id="application" :class="['set_'+$store.state.route.controller]">

		<application-header v-if="$store.state.view.header"></application-header>

		<main>
			<main-menu v-if="$store.state.view.menu" :active="sidebar === 'menu'"></main-menu>

			<application-planets-list v-if="$store.state.view.planets" :active="sidebar === 'planet'"></application-planets-list>

			<div class="main-content">
				<planet-panel v-if="$store.state.view.resources" :planet="$store.state.resources"></planet-panel>

				<application-messages-row v-for="(item, i) in messages" :key="i" :item="item"></application-messages-row>

				<div class="main-content-row">
					<error-message v-if="error" :data="error"></error-message>

					<router-view ref="router"></router-view>
				</div>
			</div>
		</main>

		<chat :visible="$store.state.route.controller !== 'chat' && $store.state.view.menu && $store.state.view.chat"></chat>

		<application-footer v-if="$store.state.view.header"></application-footer>

		<div id="ajaxLoader" :class="{active: $root.loader}"></div>
	</div>
</template>

<script>
	import MainMenu from './views/app/main-menu.vue'
	import ApplicationHeader from './views/app/header.vue'
	import ApplicationFooter from './views/app/footer.vue'
	import ApplicationPlanetsList from './views/app/planets-list.vue'
	import ApplicationMessagesRow from './views/app/messages-row.vue'
	import PlanetPanel from './views/app/planet-panel.vue'

	export default {
		name: "application",
		components: {
			MainMenu,
			ApplicationHeader,
			ApplicationFooter,
			ApplicationPlanetsList,
			ApplicationMessagesRow,
			PlanetPanel,
		},
		computed: {
			error () {
				return this.$store.state.error;
			},
			messages ()
			{
				let items = [];

				this.$store.state.messages.forEach((item) =>
				{
					if (item['type'].indexOf('-static') >= 0)
						items.push(item);
				});

				return items;
			}
		},
		data: function ()
		{
			return {
				sidebar: ''
			}
		},
		watch: {
		    '$route' () {
				this.sidebar = '';
		    }
		},
		methods: {
			sidebarToggle (type)
			{
				if (this.sidebar === type)
					this.sidebar = '';
				else
					this.sidebar = type;
			}
		},
		mounted ()
		{
			$('body').attr('page', this.$store.state.route.controller);

			this.$root.init();
		}
	}
</script>