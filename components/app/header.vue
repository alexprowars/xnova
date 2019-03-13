<template>
	<header class="top-menu">
		<div class="top-menu-container">
			<div class="top-menu-block left">
				<nuxt-link v-if="user['quests'] < 10" class="m1" to="/tutorial/" v-tooltip="'Квесты'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-book"></use>
					</svg>
					<b>{{ 10 - user['quests'] }}</b>
				</nuxt-link>
				<nuxt-link class="m1" to="/chat/" v-tooltip="'Чат'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-chat"></use>
					</svg>
				</nuxt-link>
				<nuxt-link class="m1" to="/messages/" v-tooltip="'Сообщения'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-message"></use>
					</svg>
					<b v-if="user.messages > 0">{{ user.messages }}</b>
				</nuxt-link>
				<nuxt-link v-if="user.alliance.id > 0" class="m1" to="/alliance/chat/" v-tooltip="'Альянс'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-alliance"></use>
					</svg>
					<b v-if="user.alliance.messages > 0">{{ user.alliance.messages }}</b>
				</nuxt-link>
			</div>

			<div class="top-menu-block right">
				<nuxt-link to="/stat/" class="m1" v-tooltip="'Статистика'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-statistics"></use>
					</svg>
				</nuxt-link>
				<nuxt-link to="/tech/" class="m1" v-tooltip="'Технологии'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-tech"></use>
					</svg>
				</nuxt-link>
				<nuxt-link to="/sim/" class="m1" v-tooltip="'Симулятор'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-sim"></use>
					</svg>
				</nuxt-link>
				<nuxt-link to="/search/" class="m1" v-tooltip="'Поиск'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-search"></use>
					</svg>
				</nuxt-link>
				<nuxt-link to="/support/" class="m1" v-tooltip="'Техподдержка'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-support"></use>
					</svg>
				</nuxt-link>
				<a href="http://forum.xnova.su/" target="_blank" class="m1" v-tooltip="'Форум'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-forum"></use>
					</svg>
				</a>
				<nuxt-link to="/options/" class="m1" v-tooltip="'Настройки'">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-settings"></use>
					</svg>
				</nuxt-link>
				<nuxt-link to="/logout/" class="m1" data-link="Y" v-tooltip="'Выход'">
					<svg class="icon red">
						<use xlink:href="/images/symbols.svg#icon-exit"></use>
					</svg>
				</nuxt-link>
			</div>
		</div>
	</header>
</template>

<script>
	export default {
		name: "AppHeader",
		computed: {
			user () {
				return this.$store.state.user;
			}
		}
	}
</script>