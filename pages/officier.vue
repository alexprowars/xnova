<template>
	<div class="page-officiers block">
		<div class="title">
			Доступно кредитов: {{ page['credits']|number }}
		</div>
		<div class="content container-fluid">
			<div class="row">
				<div class="d-none d-sm-block col-sm-2 text-center">
					<img :src="'/images/officiers/bigcredits.jpg'" alt="">
				</div>
				<div class="col-12 col-sm-7 text-left">
					Кредиты (<nuxt-link to="/credits/"><span class="positive">Получить</span></nuxt-link>)
					<br><br>
					Инженеры берут за свою работу только межгалактичесие кредиты. В зависимости от суммы контракта работают на вас в течении всего времени найма.
					<table class="powers">
						<tbody>
						<tr>
							<td class="padding-0">
								<img src="/images/officiers/smalcredts.gif" alt="">
							</td>
							<td>
								При помощи кредитов можно нанять инженеров
							</td>
						</tr>
						</tbody>
					</table>
				</div>
				<div class="col-sm-3 d-none d-sm-block text-center">
					<nuxt-link to="/credits/" class="button positive">
						Получить кредиты
					</nuxt-link>
				</div>
			</div>
			<OfficierRow v-for="item in page['items']" :key="item['id']" :item="item"></OfficierRow>
		</div>
	</div>
</template>

<script>
	import OfficierRow from '~/components/page/officier/row.vue'

	export default {
		name: 'officiers',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		components: {
			OfficierRow
		}
	}
</script>