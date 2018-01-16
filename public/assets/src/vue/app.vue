<template>
	<div id="application" v-bind:class="['set_'+$root.route.controller]">

		<!-- header -->
		<a v-if="$root.view.header" href="#" class="menu-toggle hidden-sm-up">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>

		<div v-if="$root.view.header" class="menu-sidebar hidden-sm-up">
			<sidebar-menu v-bind:items="$root.menu" v-bind:active="$root.getMenuActiveLink"></sidebar-menu>
		</div>

		<application-header v-if="$root.view.header"></application-header>
		<application-header-mobile-icons v-if="$root.view.header"></application-header-mobile-icons>
		<!-- end header -->

		<div class="game_content">
			<!-- menu -->
			<main-menu v-if="$root.view.menu" v-bind:items="$root.menu" v-bind:active="$root.getMenuActiveLink"></main-menu>
			<!-- end menu -->

			<!-- planets -->
			<a v-if="$root.view.planets" href="#" class="planet-toggle hidden-sm-up"><span>
					<span class="first"></span>
					<span class="second"></span>
					<span class="third"></span>
				</span>
			</a>

			<application-planets-list v-if="$root.view.planets" v-bind:items="$root.user.planets"></application-planets-list>
			<!-- end planets -->

			<div class="content">
				<!-- planet panel -->
				<planet-panel v-if="$root.view.resources" v-bind:planet="$root.resources"></planet-panel>
				<!-- end planet panel -->

				<!-- messages -->
				<div v-if="$root.messages" v-for="item in $root.messages">
					<application-messages-row v-bind:item="item"></application-messages-row>
				</div>
				<!-- end messages -->

				<div id="gamediv" class="content-row" v-html="$root.html"></div>
			</div>
		</div>

		<!-- footer -->
		<application-footer v-if="$root.view.header"></application-footer>
		<!-- end footer -->
	</div>
</template>

<script>

	export default {
		name: "app",
		components: {
			'sidebar-menu': require('./views/app/sidebar-menu.vue'),
			'main-menu': require('./views/app/main-menu.vue'),
			'application-header': require('./views/app/header.vue'),
			'application-header-mobile-icons': require('./views/app/header-mobile-icons.vue'),
			'application-footer': require('./views/app/footer.vue'),
			'application-planets-list': require('./views/app/planets-list.vue'),
			'planet-panel': require('./views/app/planet-panel.vue'),
			'application-messages-row': require('./views/app/messages-row.vue'),
		}
	}
</script>

<style scoped>

</style>