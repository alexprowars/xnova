<template>
	<div class="page-buddy">
		<div class="block">
			<div class="title text-center">
				Список друзей
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col text-center j">
							<nuxt-link to="/buddy/requests/">Запросы</nuxt-link>
						</div>
					</div>
					<div class="row">
						<div class="col text-center j">
							<nuxt-link to="/buddy/requests/my/">Мои запросы</nuxt-link>
						</div>
					</div>
					<div class="row">
						<div class="col-1 c">&nbsp;</div>
						<div class="col c">Имя</div>
						<div class="col c">Альянс</div>
						<div class="col c">Координаты</div>
						<div class="col c">Онлайн</div>
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
						<div class="col th middle">
							<nuxt-link :to="'/galaxy/?galaxy='+item['user']['galaxy']+'&system='+item['user']['system']">{{ item['user']['galaxy'] }}:{{ item['user']['system'] }}:{{ item['user']['planet'] }}</nuxt-link>
						</div>
						<div class="col th middle">
							<span v-if="item['online'] < 10" class="positive">
								В игре
							</span>
							<span v-if="item['online'] < 20" class="neutral">
								15 мин.
							</span>
							<span v-else class="negative">
								Не в игре
							</span>
						</div>
						<div class="col th middle">
							<button :to="'/buddy/delete/'+item['id']+'/'" @click.prevent="deleteItem(item['id'])" class="button text-danger">Удалить</button>
						</div>
					</div>
					<div v-if="page['items'].length === 0" class="row">
						<div class="col th">Нет друзей</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		name: 'buddy',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		methods: {
			deleteItem (id)
			{
				this.$dialog
					.confirm({
						body: 'Удалить друга?',
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