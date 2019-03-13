<template>
	<footer>
		<div class="d-none d-sm-block">
			<div class="container-fluid">
				<div class="float-left text-left">
					<nuxt-link to="/news/" title="Последние изменения">{{ $store.state['version'] }}</nuxt-link>
					<a class="d-sm-none d-md-inline" target="_blank" href="http://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
				</div>
				<div class="float-right text-right">
					<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
					<nuxt-link to="/banned/">Тёмные</nuxt-link>|
					<a href="//vk.com/xnova_game" target="_blank">ВК</a>|
					<nuxt-link to="/contacts/">Контакты</nuxt-link>|
					<nuxt-link to="/content/help/">Новичкам</nuxt-link>|
					<nuxt-link to="/content/agb/">Правила</nuxt-link>|
					<a @click.prevent title="Игроков в сети" style="color:green">{{ $store.state['stats']['online'] }}</a>/<a @click.prevent title="Всего игроков" style="color:yellow">{{ $store.state['stats']['users'] }}</a>
				</div>
				<div class="clearfix"></div>
			</div>
		</div>
		<div class="row d-sm-none footer-mobile">
			<div class="col-12 text-center">
				<a href="http://forum.xnova.su/" target="_blank">Форум</a>|
				<nuxt-link to="/banned/">Тёмные</nuxt-link>|
				<nuxt-link to="/contacts/">Контакты</nuxt-link>|
				<nuxt-link to="/content/help/">Новичкам</nuxt-link>|
				<nuxt-link to="/content/agb/">Правила</nuxt-link>
			</div>
			<div class="col-8 text-center">
				<nuxt-link to="/news/" title="Последние изменения">{{ $store.state.version }}</nuxt-link>
				<a class="media_1" target="_blank" href="http://xnova.su/">© 2008 - {{ (new Date).getFullYear() }} Xcms</a>
			</div>
			<div class="col-4 text-center">
				<a @click.prevent title="Игроков в сети" style="color:green">{{ $store.state['stats']['online'] }}</a>/<a @click.prevent title="Всего игроков" style="color:yellow">{{ $store.state['stats']['users'] }}</a>
			</div>
		</div>
	</footer>
</template>

<script>
	export default {
		name: "AppFooter"
	}
</script>