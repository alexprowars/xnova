<template>
	<div id="application" :class="['set_'+$store.state.route.controller]">

		<AppHeader v-if="$store.state['view']['header']"></AppHeader>

		<main>
			<MainMenu v-if="$store.state['view']['menu']" :active="sidebar === 'menu'"></MainMenu>

			<PlanetsList v-if="$store.state['view']['planets']" :active="sidebar === 'planet'"></PlanetsList>

			<div class="main-content">

				<PlanetPanel v-if="$store.state['view']['resources']" :planet="$store.state.resources"></PlanetPanel>

				<MessagesRow v-for="(item, i) in messages" :key="i" :item="item"></MessagesRow>

				<div class="main-content-row">
					<error-message v-if="error" :data="error"></error-message>

					<nuxt></nuxt>
				</div>

			</div>
		</main>

		<no-ssr>
			<chat :visible="$store.state.route.controller !== 'chat' && $store.state['view']['menu'] && $store.state.view.chat"></chat>
		</no-ssr>

		<AppFooter v-if="$store.state['view']['header']"></AppFooter>

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
	import { tooltip, swipe } from '../utils/jquery'

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
				sidebar: ''
			}
		},
		head () {
			return {
				title: this.$store.state.title,
				bodyAttrs: {
					page: this.$store.state.route.controller
				}
			}
		},
		watch: {
		    '$route' () {
				this.sidebar = '';
		    },
			redirect (val)
			{
				if (val && val.length > 0)
					window.location.href = val;
			},
			notifications (val)
			{
				val.forEach((item) =>
				{
					$.toast({
						text: item.text,
						icon: item.type
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
			init ()
			{
				if (!this.$store.state.mobile)
				{
					tooltip()
					swipe()
				}

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
			this.init();
		}
	}
</script>