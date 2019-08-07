<template>
	<div class="page-fleet-shortcut">
		<div class="block">
			<div class="title">
				Быстрые ссылки
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div v-for="(item, i) in page['items']" class="col-6 th">
							<nuxt-link :to="'/fleet/shortcut/view/'+i+'/'">{{ item['name'] }} [{{ item['galaxy'] }}:{{ item['system'] }}:{{ item['planet'] }}] {{ item['type'] }}</nuxt-link>
						</div>
						<div v-if="page['items'].length === 0" class="col th">Список ссылок пуст</div>
					</div>
					<div class="row">
						<div class="col c">
							<nuxt-link to="/fleet/shortcut/add/" class="button">Добавить</nuxt-link>
							<nuxt-link to="/fleet/" class="button">Назад</nuxt-link>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'fleet-shortcut',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
	}
</script>