<template>
	<header class="game_menu">
		<div class="d-sm-none text-center bar">
			<a v-if="user['tutorial'] < 10" class="m1 tooltip" :href="$root.getUrl('tutorial/')" data-content="Квесты">
				<svg class="icon">
					<use xlink:href="#icon-book"></use>
				</svg>
			</a>
			<a class="m1 tooltip" :href="$root.getUrl('chat/')" data-content="Чат">
				<svg class="icon">
					<use xlink:href="#icon-chat"></use>
				</svg>
			</a>
			<a class="m1 tooltip" :href="$root.getUrl('messages/')" data-content="Сообщения">
				<svg class="icon">
					<use xlink:href="#icon-message"></use>
				</svg>
				<b v-if="user.messages > 0">{{ user.messages }}</b>
			</a>
			<a v-if="user.alliance.id > 0" class="m1 tooltip" :href="$root.getUrl('alliance/chat/')" data-content="Альянс">
				<svg class="icon">
					<use xlink:href="#icon-alliance"></use>
				</svg>
				<b v-if="user.alliance.messages > 0">{{ user.alliance.messages }}</b>
			</a>
		</div>
		<div class="bar d-none d-sm-flex">
			<div class="message_list">
				<a v-if="user['tutorial'] < 10" class="m1 tooltip" :href="$root.getUrl('tutorial/')" data-content="Квесты">
					<svg class="icon">
						<use xlink:href="#icon-book"></use>
					</svg>
				</a>
				<a class="m1 tooltip" :href="$root.getUrl('chat/')" data-content="Чат">
					<svg class="icon">
						<use xlink:href="#icon-chat"></use>
					</svg>
				</a>
				<a class="m1 tooltip" :href="$root.getUrl('messages/')" data-content="Сообщения">
					<svg class="icon">
						<use xlink:href="#icon-message"></use>
					</svg>
					<b v-if="user.messages > 0">{{ user.messages }}</b>
				</a>
				<a v-if="user.alliance.id > 0" class="m1 tooltip" :href="$root.getUrl('alliance/chat/')" data-content="Альянс">
					<svg class="icon">
						<use xlink:href="#icon-alliance"></use>
					</svg>
					<b v-if="user.alliance.messages > 0">{{ user.alliance.messages }}</b>
				</a>
			</div>
			<div class="top_menu">
				<a :href="$root.getUrl('stat/')" class="tooltip m1" data-content="Статистика">
					<svg class="icon">
						<use xlink:href="#icon-statistics"></use>
					</svg>
				</a>
				<a :href="$root.getUrl('tech/')" class="tooltip m1" data-content="Технологии">
					<svg class="icon">
						<use xlink:href="#icon-tech"></use>
					</svg>
				</a>
				<a :href="$root.getUrl('sim/')" class="tooltip m1" data-content="Симулятор">
					<svg class="icon">
						<use xlink:href="#icon-sim"></use>
					</svg>
				</a>
				<a :href="$root.getUrl('search/')" class="tooltip m1" data-content="Поиск">
					<svg class="icon">
						<use xlink:href="#icon-search"></use>
					</svg>
				</a>
				<a :href="$root.getUrl('support/')" class="tooltip m1" data-content="Техподдержка">
					<svg class="icon">
						<use xlink:href="#icon-support"></use>
					</svg>
				</a>
				<a href="http://forum.xnova.su/" target="_blank" class="tooltip m1" data-content="Форум">
					<svg class="icon">
						<use xlink:href="#icon-forum"></use>
					</svg>
				</a>
				<a :href="$root.getUrl('options/')" class="tooltip m1" data-content="Настройки">
					<svg class="icon">
						<use xlink:href="#icon-settings"></use>
					</svg>
				</a>
				<a :href="$root.getUrl('logout/')" class="tooltip m1" data-link="Y" data-content="Выход">
					<svg class="icon red">
						<use xlink:href="#icon-exit"></use>
					</svg>
				</a>
			</div>
		</div>
	</header>
</template>

<script>
	export default {
		name: "application-header",
		computed: {
			user () {
				return this.$store.state.user;
			}
		}
	}
</script>