<template>
	<div class="page-buddy page-buddy-request">
		<div class="block">
			<div class="title text-center">
				{{ page['title'] }}
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col-1 c">&nbsp;</div>
						<div class="col c">Имя</div>
						<div class="col c">Альянс</div>
						<div class="col-2 c">Координаты</div>
						<div class="col c">Текст запроса</div>
						<div class="col c">&nbsp;</div>
					</div>
					<div v-for="(item, i) in page['items']" class="row">
						<div class="col-1 th middle">
							{{ i + 1 }}
						</div>
						<div class="col th middle">
							<nuxt-link :to="'/messages/write/'+item['user']['id']+'/'">{{ item['user']['name'] }}</nuxt-link>
						</div>
						<div class="col th middle">
							<nuxt-link v-if="item['user']['alliance']['id'] > 0" :to="'/alliance/info/'+item['user']['alliance']['id']+'/'">{{ item['user']['alliance']['name'] }}</nuxt-link>
							<template v-else>-</template>
						</div>
						<div class="col-2 th middle">
							<nuxt-link :to="'/galaxy/?galaxy='+item['user']['galaxy']+'&system='+item['user']['system']">{{ item['user']['galaxy'] }}:{{ item['user']['system'] }}:{{ item['user']['planet'] }}</nuxt-link>
						</div>
						<div class="col th middle" v-html="item['text']">	</div>
						<div class="col th text-center">
							<button v-if="page['isMy']" @click.prevent="deleteRequest(item['id'])" class="button text-danger">Удалить запрос</button>
							<template v-else>
								<button @click.prevent="approveRequest(item['id'])" class="button text-success">Применить</button>
								<button @click.prevent="deleteRequest(item['id'])" class="button text-danger">Отклонить</button>
							</template>
						</div>
					</div>
					<div v-if="page['items'].length === 0" class="row">
						<div class="col th">Нет запросов</div>
					</div>
				</div>
			</div>
		</div>
		<div class="separator"></div>
		<div class="text-right">
			<div class="row">
				<div class="col">
					<nuxt-link to="/buddy/" class="button">Вернуться назад</nuxt-link>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'buddy-requests',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		methods: {
			approveRequest (id)
			{
				this.$post('/buddy/approve/'+id+'/')
				.then((result) => {
					this.$store.commit('PAGE_LOAD', result);
				})
			},
			deleteRequest (id)
			{
				this.$dialog
					.confirm({
						body: 'Удалить запрос?',
					}, {
						okText: 'Да',
						cancelText: 'Нет',
					})
					.then(() =>
					{
						this.$post('/buddy/delete/'+id+'/')
						.then((result) => {
							this.$store.commit('PAGE_LOAD', result);
						})
					})
			},
		}
	}
</script>