<template>
	<nav class="main-menu">
		<a :class="{active: active}" class="menu-toggle d-sm-none" @click.prevent="$emit('toggle')">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>

		<div :class="{active: active}" class="menu-sidebar">
			<ul>
				<li is="main-menu-item" v-for="(item, i) in items" :item="item" :key="i"></li>
			</ul>
		</div>
	</nav>
</template>

<script>
	import MainMenuItem from './main-menu-item.vue'

	export default {
		name: "main-menu",
		components: {
			MainMenuItem
		},
		props: {
			active: {
				type: Boolean,
				default: true
			}
		},
		data () {
			return {
				items: [
					{ id: 'overview', title: 'Обзор', url: '/overview/' },
					{ id: 'imperium', title: 'Империя', url: '/imperium/' },
					{ id: 'galaxy', title: 'Космос', url: '/galaxy/' },
					{ id: 'fleet', title: 'Флот', url: '/fleet/' },
					{ id: 'buildingsindex', title: 'Постройки', url: '/buildings/' },
					{ id: 'buildingsresearch', title: 'Наука', url: '/buildings/research/' },
					{ id: 'buildingsfleet', title: 'Верфь', url: '/buildings/fleet/' },
					{ id: 'buildingsdefense', title: 'Оборона', url: '/buildings/defense/' },
					{ id: 'resources', title: 'Сырьё', url: '/resources/' },
					{ id: 'merchant', title: 'Рынок', url: '/merchant/' },
					{ id: 'officier', title: 'Офицеры', url: '/officier/' },
					{ id: 'alliance', title: 'Альянс', url: '/alliance/' },
					{ id: 'buddy', title: 'Друзья', url: '/buddy/' },
					{ id: 'notes', title: 'Заметки', url: '/notes/' },
					{ id: 'records', title: 'Рекорды', url: '/records/' },
					{ id: 'hall', title: 'Зал славы', url: '/hall/' },
					{ id: 'log', title: 'Логи', url: '/log/' },
				]
			}
		},
	}
</script>