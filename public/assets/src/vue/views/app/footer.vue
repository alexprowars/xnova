<template>
	<footer>
		<div class="hidden-xs-down">
			<div class="container-fluid">
				<div class="pull-xs-left text-xs-left">
					<a v-bind:href="$root.getUrl('news/')" title="Последние изменения">{{ $root.version }}</a>
					<a class="hidden-sm-down" target="_blank" href="http://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
				</div>
				<div class="pull-xs-right text-xs-right">
					<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
					<a v-bind:href="$root.getUrl('banned/')">Тёмные</a>|
					<a href="//vk.com/xnova_game" target="_blank">ВК</a>|
					<a v-bind:href="$root.getUrl('contact/')">Контакты</a>|
					<a v-bind:href="$root.getUrl('content/help/')">Новичкам</a>|
					<a v-bind:href="$root.getUrl('content/agb/')">Правила</a>|
					<a onclick="" title="Игроков в сети" style="color:green">{{ $root.stats.online }}</a>/<a onclick="" title="Всего игроков" style="color:yellow">{{ $root.stats.users }}</a>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="row hidden-sm-up footer-mobile">
			<div class="col-xs-12 text-xs-center">
				<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
				<a v-bind:href="$root.getUrl('banned/')">Тёмные</a>|
				<a v-bind:href="$root.getUrl('contact/')">Контакты</a>|
				<a v-bind:href="$root.getUrl('content/help/')">Новичкам</a>|
				<a v-bind:href="$root.getUrl('content/agb/')">Правила</a>
			</div>
			<div class="col-xs-8 text-xs-center">
				<a v-bind:href="$root.getUrl('news/')" title="Последние изменения">{{ $root.version }}</a>
				<a class="media_1" target="_blank" href="http://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
			</div>
			<div class="col-xs-4 text-xs-center">
				<a onclick="" title="Игроков в сети" style="color:green">{{ $root.stats.online }}</a>/<a onclick="" title="Всего игроков" style="color:yellow">{{ $root.stats.users }}</a>
			</div>
		</div>
	</footer>
</template>

<script>
	export default {
		name: "application-footer"
	}
</script>