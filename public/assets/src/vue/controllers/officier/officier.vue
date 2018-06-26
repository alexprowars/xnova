<template>
	<div v-if="page" class="page-officiers block">
		<div class="title">
			Доступно кредитов: {{ page['credits']|number }}
		</div>
		<div class="content container-fluid">
			<div class="row">
				<div class="d-none d-sm-block col-sm-2 text-center">
					<img :src="$root.getUrl('assets/images/officiers/bigcredits.jpg')" alt="">
				</div>
				<div class="col-12 col-sm-7 text-left">
					Кредиты (<router-link to="/credits/"><span class="positive">Получить</span></router-link>)
					<br><br>
					Инженеры берут за свою работу только межгалактичесие кредиты. В зависимости от суммы контракта работают на вас в течении всего времени найма.
					<table class="powers">
						<tr>
							<td class="padding-0">
								<img :src="$root.getUrl('assets/images/officiers/smalcredts.gif')">
							</td>
							<td>
								При помощи кредитов можно нанять инженеров
							</td>
						</tr>
					</table>
				</div>
				<div class="col-sm-3 d-none d-sm-block text-center">
					<router-link to="/credits/" class="button positive">
						Получить кредиты
					</router-link>
				</div>
			</div>
			<officier-row v-for="item in page['items']" :key="item['id']" :item="item"></officier-row>
		</div>
	</div>
</template>

<script>
	import OfficierRow from './officier-row.vue'
	import router from 'router-mixin'

	export default {
		name: "officiers",
		mixins: [router],
		components: {
			OfficierRow
		}
	}
</script>