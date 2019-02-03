<template>
	<div v-if="page" class="page-fleet-shortcut">
		<div class="block">
			<div class="title">
				Быстрые ссылки
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div v-for="(item, i) in page['items']" class="col-6 th">
							<router-link :to="'/fleet/shortcut/view/'+i+'/'">{{ item['name'] }} [{{ item['galaxy'] }}:{{ item['system'] }}:{{ item['planet'] }}] {{ item['type'] }}</router-link>
						</div>
						<div v-if="page['items'].length === 0" class="col th">Список ссылок пуст</div>
					</div>
					<div class="row">
						<div class="col c">
							<router-link to="/fleet/shortcut/add/new/" class="button">Добавить</router-link>
							<router-link to="/fleet/" class="button">Назад</router-link>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import router from 'router-mixin'

	export default {
		name: "fleet-shortcut",
		mixins: [router]
	}
</script>