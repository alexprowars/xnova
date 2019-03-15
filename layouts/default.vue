<template>
	<div id="application" :class="['set_'+$store.state.route.controller, (!loader ? 'preload' : '')]" v-touch:swipe.left.right="swipe">
		<AppHeader v-if="$store.state['view']['header']"/>
		<main>
			<MainMenu v-if="$store.state['view']['menu']" :active="sidebar === 'menu'"/>
			<PlanetsList v-if="$store.state['view']['planets']" :active="sidebar === 'planet'"/>
			<div class="main-content" v-touch:tap="tap">
				<PlanetPanel v-if="$store.state['view']['resources']"/>
				<MessagesRow v-for="(item, i) in messages" :key="i" :item="item"/>
				<div class="main-content-row">
					<error-message v-if="error" :data="error"/>
					<nuxt/>
				</div>
			</div>
		</main>

		<no-ssr>
			<chat v-if="$store.getters.isAuthorized()" :visible="$store.state.route.controller !== 'chat' && $store.state['view']['menu'] && $store.state.view.chat"></chat>
		</no-ssr>

		<AppFooter v-if="$store.state['view']['header']"/>

		<div id="ajaxLoader" :class="{active: $store.state.loading}"></div>

		<no-ssr>
			<modals-container/>
		</no-ssr>
	</div>
</template>

<script>
	import MainMenu from '../components/app/main-menu.vue'
	import AppHeader from '../components/app/header.vue'
	import AppFooter from '../components/app/footer.vue'
	import PlanetsList from '../components/app/planets-list.vue'
	import MessagesRow from '../components/app/messages-row.vue'
	import PlanetPanel from '../components/app/planet-panel.vue'
	import { addScript } from '~/utils/helpers'

	export default {
		name: "application",
		components: {
			MainMenu,
			AppHeader,
			AppFooter,
			PlanetsList,
			MessagesRow,
			PlanetPanel,
		},
		computed: {
			error () {
				return this.$store.state.error;
			},
			redirect () {
				return this.$store.state.redirect;
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
			},
			notifications ()
			{
				let items = [];

				this.$store.state.messages.forEach((item) =>
				{
					if (item['type'].indexOf('-static') < 0)
						items.push(item);
				});

				return items;
			}
		},
		data ()
		{
			return {
				sidebar: '',
				loader: false,
			}
		},
		head () {
			return {
				title: this.$store.state.title,
				titleTemplate: '%s | Звездная Империя',
				htmlAttrs: {
					lang: 'ru',
				},
				bodyAttrs: {
					page: this.$store.state.route.controller,
					class: this.$store.state.isSocial ? 'iframe' : 'window'
				},
				meta: [
					{ hid: 'og:title', property: 'og:title', content: this.$store.state.title }
				]
			}
		},
		watch: {
		    '$route' () {
				this.sidebar = '';
		    },
			redirect (val)
			{
				if (val && val.length > 0)
					this.$router.push(val);
			},
			notifications (val)
			{
				val.forEach((item) =>
				{
					this.$toasted.show(item.text, {
						type: item.type
					});
				})
			},
		},
		methods: {
			sidebarToggle (type)
			{
				if (this.sidebar === type)
					this.sidebar = '';
				else
					this.sidebar = type;
			},
			swipe (direction, ev)
			{
				if (!this.$store.getters.isMobile)
					return

				if (ev.target.closest('.table-responsive') !== null)
					return;

				if (direction === 'left')
					this.sidebar = 'planet'

				if (direction === 'right')
					this.sidebar = 'menu'
			},
			tap ()
			{
				if (!this.$store.getters.isMobile)
					return

				if (this.sidebar !== '')
					this.sidebar = ''
			},
			init ()
			{
				if (typeof VK !== 'undefined')
				{
					try
					{
						VK.init(() =>
						{
							console.log('vk init success');

							setInterval(() =>
							{
								let height = 0;

								$('#application .main-content > div').each(function() {
									height += $(this).height();
								});

								VK.callMethod("resizeWindow", 1000, (height < 600 ? 700 : height + 200));

							}, 1000);
						},
						() => {}, '5.74');
					}
					catch (e) {}
				}
			},
		},
		mounted ()
		{
			if (this.$store.state.isSocial)
				addScript('https://vk.com/js/api/xd_connection.js')

			this.init();
			this.loader = true;
		}
	}
</script>