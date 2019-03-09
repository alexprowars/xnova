<template>
	<header class="top-menu">
		<div class="top-menu-container">
			<div class="top-menu-block left">
				<router-link v-if="user['quests'] < 10" class="m1 tooltip" to="/tutorial/" data-content="Квесты">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-book"></use>
					</svg>
					<b>{{ 10 - user['quests'] }}</b>
				</router-link>
				<router-link class="m1 tooltip" to="/chat/" data-content="Чат">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-chat"></use>
					</svg>
				</router-link>
				<router-link class="m1 tooltip" to="/messages/" data-content="Сообщения">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-message"></use>
					</svg>
					<b v-if="user.messages > 0">{{ user.messages }}</b>
				</router-link>
				<router-link v-if="user.alliance.id > 0" class="m1 tooltip" to="/alliance/chat/" data-content="Альянс">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-alliance"></use>
					</svg>
					<b v-if="user.alliance.messages > 0">{{ user.alliance.messages }}</b>
				</router-link>
			</div>

			<div class="top-menu-block right">
				<router-link to="/stat/" class="tooltip m1" data-content="Статистика">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-statistics"></use>
					</svg>
				</router-link>
				<router-link to="/tech/" class="tooltip m1" data-content="Технологии">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-tech"></use>
					</svg>
				</router-link>
				<router-link to="/sim/" class="tooltip m1" data-content="Симулятор">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-sim"></use>
					</svg>
				</router-link>
				<router-link to="/search/" class="tooltip m1" data-content="Поиск">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-search"></use>
					</svg>
				</router-link>
				<router-link to="/support/" class="tooltip m1" data-content="Техподдержка">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-support"></use>
					</svg>
				</router-link>
				<a href="http://forum.xnova.su/" target="_blank" class="tooltip m1" data-content="Форум">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-forum"></use>
					</svg>
				</a>
				<router-link to="/options/" class="tooltip m1" data-content="Настройки">
					<svg class="icon">
						<use xlink:href="/images/symbols.svg#icon-settings"></use>
					</svg>
				</router-link>
				<router-link to="/logout/" class="tooltip m1" data-link="Y" data-content="Выход">
					<svg class="icon red">
						<use xlink:href="/images/symbols.svg#icon-exit"></use>
					</svg>
				</router-link>
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