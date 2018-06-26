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

					<router-view></router-view>
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
		data ()
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
			},
			init ()
			{
				let body = $('body');
				let app = this.$root;

				body.on('mouseenter', '.tooltip', function()
				{
					if (app.$store.state.mobile)
						return;

					let _this = $(this);

					let status = false;

					try {
						status = _this.tooltipster('status');
					} catch (err) {}

					if (status)
						return;

					let maxWidth = null;

					if (_this.data('width') !== undefined)
						maxWidth = parseInt(_this.data('width'));

					_this.tooltipster({
						delay: 100,
						distance: 0,
						maxWidth: maxWidth,
						contentAsHTML: true,
						interactive: _this.hasClass('sticky'),
						functionInit: function(instance)
						{
							if (_this.hasClass('script'))
								instance.content(eval(_this.data('content')));
							else if (typeof _this.data('content') === "undefined")
								instance.content(_this.find('.tooltip-content'));
							else
								instance.content(_this.data('content'));
						}
					}).tooltipster('open');
				})
				.on('click', '.tooltip', function()
				{
					if (!app.$store.state.mobile)
						return;

					let _this = $(this);

					let status = false;

					try {
						status = _this.tooltipster('status');
					} catch (err) {}

					if (!_this.hasClass('sticky') && status)
					{
						if (status.open)
							_this.tooltipster('close');
						else
							_this.tooltipster('open');

						return;
					}

					if (typeof _this.data('tooltipster-ns') !== 'undefined')
					{
						_this.tooltipster('open');
						return;
					}

					let maxWidth = null;

					if (_this.data('width') !== undefined)
						maxWidth = parseInt(_this.data('width'));

					_this.tooltipster({
						delay: 100,
						distance: 0,
						maxWidth: maxWidth,
						contentAsHTML: true,
						interactive: _this.hasClass('sticky'),
						functionInit: function(instance)
						{
							if (_this.hasClass('script'))
								instance.content(eval(_this.data('content')));
							else if (typeof _this.data('content') === "undefined")
								instance.content(_this.find('.tooltip-content'));
							else
								instance.content(_this.data('content'));
						}
					}).tooltipster('open');
				})

				body.find('.main-content')
				.on('click', '.page-html a', function(e)
				{
					let el = $(this);
					let url = el.attr('href');

					if (!url || el.hasClass('skip') || url.indexOf('#') === 0)
						return false;

					if (url.indexOf('javascript') === 0 || url.indexOf('mailto') === 0 || url.indexOf('#') >= 0 || el.attr('target') === '_blank')
						return true;
					else
					{
						e.preventDefault();

						app.load(url);
					}

					return false;
				})
				.on('click', 'form:not(.noajax) input[type=submit], form[class!=noajax] button[type=submit]', function(e)
				{
					e.preventDefault();

					let button = $(this);
					let form = button.closest('form');

					form.append($('<input/>', {type: 'hidden', name: button.attr('name'), value: button.attr('value')}));
					form.submit();
				})
				.on('submit', 'form[class!=noajax]', function(e)
				{
					e.preventDefault();

					let form = $(this);

					app.loader = true;

					let formData = new FormData(this);

					$post(form.attr('action'), formData)
					.then((result) => {
						app.$store.commit('PAGE_LOAD', result)
					}, () => {
						alert('Что-то пошло не так!? Попробуйте еще раз');
					})
					.then(() => {
						app.loader = false;
					})
				});
			},
		},
		mounted ()
		{
			$('body').attr('page', this.$store.state.route.controller);

			this.init();
		}
	}
</script>