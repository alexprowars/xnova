<template>
	<div class="page-support">
		<div class="block">
			<div class="title text-center">
				Служба техподдержки
			</div>
			<div class="content border-0">
				<div class="table">
					<div v-if="!page['items'].length" class="row">
						<div class="col th">Нет запросов в техподдержку</div>
					</div>
					<div v-else class="row">
						<div class="col-1 th">ID</div>
						<div class="col-6 th">Тема</div>
						<div class="col-2 th">Статус</div>
						<div class="col-3 th">Дата</div>
					</div>
					<div v-for="item in page['items']" class="row">
						<div class="col-1 c">
							<a @click="getInfo(item['id'])">{{ item['id'] }}</a>
						</div>
						<div class="col-6 c">
							<a @click="getInfo(item['id'])">{{ item['subject'] }}</a>
						</div>
						<div class="col-2 c">
							<span v-if="item['status'] === 0" style="color:red">закрыто</span>
							<span v-if="item['status'] === 1" style="color:green">открыто</span>
							<span v-if="item['status'] === 2" style="color:orange">ответ админа</span>
							<span v-if="item['status'] === 3" style="color:green">ответ игрока</span>
						</div>
						<div class="col-3 c">{{ item['date'] }}</div>
					</div>
				</div>
			</div>
		</div>

		<support-detail v-if="detail" :item="detail" @close="detail = false"></support-detail>

		<div v-if="!request">
			<div class="separator"></div>
			<div class="row">
				<div class="col text-right">
					<button @click="newRequest">Создать запрос</button>
				</div>
			</div>
		</div>
		<support-new v-else @close="request = false"></support-new>
	</div>
</template>

<script>
	import SupportDetail from '~/components/page/support/detail.vue'
	import SupportNew from '~/components/page/support/new.vue'

	export default {
		name: 'support',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		data () {
			return {
				detail: false,
				request: false
			}
		},
		components: {
			SupportDetail,
			SupportNew
		},
		methods: {
			newRequest () {
				this.request = !this.request
			},
			getInfo (id)
			{
				this.$get('/support/info/'+id+'/')
				.then((result) => {
					this.detail = result
				})
			}
		}
	}
</script>