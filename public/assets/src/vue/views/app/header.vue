<template>
	<header class="game_menu">
		<div class="d-sm-none text-center bar">
			<a v-if="user.tutorial < 10" class="m1 tooltip" v-bind:href="$root.getUrl('tutorial/')" data-content="Квесты"><span class="sprite ico_tutorial"></span></a>
			<a class="m1 tooltip" v-bind:href="$root.getUrl('chat/')" data-content="Чат"><span class="sprite ico_chat"></span></a>
			<a class="m1 tooltip" v-bind:href="$root.getUrl('messages/')" data-content="Сообщения"><span class="sprite ico_mail"></span> <b>{{ user.messages }}</b></a>
			<a v-if="user.alliance.id > 0" class="m1 tooltip" v-bind:href="$root.getUrl('alliance/chat/')" data-content="Альянс"><span class="sprite ico_alliance"></span> <b>{{ user.alliance.messages }}</b></a>
		</div>
		<div class="bar d-none d-sm-flex">
			<div class="message_list">
				<div class="message_list">
					<a v-if="user.tutorial < 10" class="m1 tooltip" v-bind:href="$root.getUrl('tutorial/')" data-content="Квесты"><span class="sprite ico_tutorial"></span></a>
					<a class="m1 tooltip" v-bind:href="$root.getUrl('chat/')" data-content="Чат"><span class="sprite ico_chat"></span></a>
					<a class="m1 tooltip" v-bind:href="$root.getUrl('messages/')" data-content="Сообщения"><span class="sprite ico_mail"></span> <b>{{ user.messages }}</b></a>
					<a v-if="user.alliance.id > 0" class="m1 tooltip" v-bind:href="$root.getUrl('alliance/chat/')" data-content="Альянс"><span class="sprite ico_alliance"></span> <b>{{ user.alliance.messages }}</b></a>
				</div>
			</div>
			<div class="top_menu">
				<a v-bind:href="$root.getUrl('stat/')" class="tooltip m1" data-content="Статистика"><span class="sprite ico_stats"></span></a>
				<a v-bind:href="$root.getUrl('tech/')" class="tooltip m1" data-content="Технологии"><span class="sprite ico_tech"></span></a>
				<a v-bind:href="$root.getUrl('sim/')" class="tooltip m1" data-content="Симулятор"><span class="sprite ico_sim"></span></a>
				<a v-bind:href="$root.getUrl('search/')" class="tooltip m1" data-content="Поиск"><span class="sprite ico_search"></span></a>
				<a v-bind:href="$root.getUrl('support/')" class="tooltip m1" data-content="Техподдержка"><span class="sprite ico_support"></span></a>
				<a href="http://forum.xnova.su/" target="_blank" class="tooltip m1" data-content="Форум"><span class="sprite ico_forum"></span></a>
				<a v-bind:href="$root.getUrl('options/')" class="tooltip m1" data-content="Настройки"><span class="sprite ico_settings"></span></a>
				<a v-bind:href="$root.getUrl('logout/')" class="tooltip m1" data-link="Y" data-content="Выход"><span class="sprite ico_exit"></span></a>
			</div>
		</div>
	</header>
</template>

<script>
	export default {
		name: "application-header",
		computed: {
			user ()
			{
				return this.$store.state.user;
			}
		}
	}
</script>