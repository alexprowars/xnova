<template>
	<footer>
		<div class="d-none d-sm-block">
			<div class="container-fluid">
				<div class="float-left text-left">
					<a v-bind:href="$root.getUrl('news/')" title="Последние изменения">{{ $store.state.version }}</a>
					<a class="d-sm-none d-md-inline" target="_blank" href="http://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
				</div>
				<div class="float-right text-right">
					<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
					<a v-bind:href="$root.getUrl('banned/')">Тёмные</a>|
					<a href="//vk.com/xnova_game" target="_blank">ВК</a>|
					<a v-bind:href="$root.getUrl('contact/')">Контакты</a>|
					<a v-bind:href="$root.getUrl('content/help/')">Новичкам</a>|
					<a v-bind:href="$root.getUrl('content/agb/')">Правила</a>|
					<a onclick="" title="Игроков в сети" style="color:green">{{ $store.state.stats.online }}</a>/<a onclick="" title="Всего игроков" style="color:yellow">{{ $store.state.stats.users }}</a>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="row d-sm-none footer-mobile">
			<div class="col-12 text-center">
				<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
				<a v-bind:href="$root.getUrl('banned/')">Тёмные</a>|
				<a v-bind:href="$root.getUrl('contact/')">Контакты</a>|
				<a v-bind:href="$root.getUrl('content/help/')">Новичкам</a>|
				<a v-bind:href="$root.getUrl('content/agb/')">Правила</a>
			</div>
			<div class="col-8 text-center">
				<a v-bind:href="$root.getUrl('news/')" title="Последние изменения">{{ $store.state.version }}</a>
				<a class="media_1" target="_blank" href="http://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
			</div>
			<div class="col-4 text-center">
				<a onclick="" title="Игроков в сети" style="color:green">{{ $store.state.stats.online }}</a>/<a onclick="" title="Всего игроков" style="color:yellow">{{ $store.state.stats.users }}</a>
			</div>
		</div>
	</footer>
</template>

<script>
	export default {
		name: "application-footer"
	}
</script>