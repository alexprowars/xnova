<template>
	<div class="page-resources">
		<div class="table">
			<div class="row">
				<div class="col-5 c">Уровень производства</div>
				<div class="col-2 th">{{ page['production_level'] }}%</div>
				<div class="col-5 th">
					<ResourcesBar :value="page['production_level']" :reverse="true"></ResourcesBar>
				</div>
			</div>
			<div class="row">
				<div class="col-5 c">
					<nuxt-link to="/info/113/">{{ $t('TECH.113') }}</nuxt-link>
				</div>
				<div class="col-2 th">
					{{ page['energy_tech'] }} ур.
				</div>
				<div class="col-5 th"></div>
			</div>
		</div>

		<div class="separator"></div>

		<div class="block">
			<div class="title text-center">
				Управление шахтами и энергетикой
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col col-sm-6 th">
							<nuxt-link to="/resources/?production&active=Y" class="button">
								Включить на всех<br>планетах
							</nuxt-link>
						</div>
						<div class="col col-sm-6 th">
							<nuxt-link to="/resources/?production&active=N" class="button">
								Выключить на всех<br>планетах
							</nuxt-link>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="block">
			<div class="title text-center">
				Производство на планете {{ page['planet_name'] }}
			</div>
			<div class="content border-0">
				<div class="table-responsive">
					<router-form action="/resources/">
						<table class="table">
							<tbody>
								<tr>
									<th width="200"></th>
									<th>Ур.</th>
									<th>Бонус</th>
									<th><popup-link to="/info/1/" :title="$t('TECH.1')">Металл</popup-link></th>
									<th><popup-link to="/info/2/" :title="$t('TECH.2')">Кристалл</popup-link></th>
									<th><popup-link to="/info/3/" :title="$t('TECH.3')">Дейтерий</popup-link></th>
									<th><popup-link to="/info/4/" :title="$t('TECH.4')">Энергия</popup-link></th>
									<th width="100">КПД</th>
								</tr>
								<tr>
									<th class="text-left" nowrap>Базовое производство</th>
									<td class="k">-</td>
									<td class="k">-</td>
									<td v-for="res in page['resources']" class="k">{{ page['production'][res]['basic'] }}</td>
									<td class="k">{{ page['production']['energy']['basic'] }}</td>
									<td class="k">100%</td>
								</tr>
								<tr is="resources-row" v-for="(item, index) in page['items']" :key="index" :item="item" :resources="page['resources']"></tr>
								<tr>
									<th colspan="2">Вместимость:</th>
									<th>{{ page['bonus_h'] }}%</th>
									<td v-for="res in page['resources']" class="k" v-once>
										<span :class="[(page['production'][res]['max'] > $store.state['resources'][res]['current']) ? 'positive' : 'negative']">
											{{(page['production'][res]['max'] / 1000)|number }} k
										</span>
									</td>
									<td class="k">
										<font color="#00ff00">{{ page['production']['energy']['max']|number }}</font>
									</td>
									<td class="k">
										<input name="action" value="Пересчитать" type="submit">
									</td>
								</tr>
								<tr>
									<th colspan="3">Сумма:</th>
									<td v-for="res in page['resources']" class="k">
										<colored :value="page['production'][res]['total']"></colored>
									</td>
									<td class="k">{{ page['production']['energy']['total']|number }}</td>
								</tr>
							</tbody>
						</table>
					</router-form>
				</div>
			</div>
		</div>

		<div class="block">
			<div class="title text-center">
				Информация о производстве
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col-2 th">&nbsp;</div>
						<div class="col-2 th">Час</div>
						<div class="col-2 th">День</div>
						<div class="col-3 th">Неделя</div>
						<div class="col-3 th">Месяц</div>
					</div>
					<div class="row" v-for="res in page['resources']">
						<div class="col-2 th">
							{{ $t('RESOURCES.'+res) }}
						</div>
						<div class="col-2 th">
							<colored :value="page['production'][res]['total']"></colored>
						</div>
						<div class="col-2 th">
							<colored :value="page['production'][res]['total'] * 24"></colored>
						</div>
						<div class="col-3 th">
							<colored :value="page['production'][res]['total'] * 24 * 7"></colored>
						</div>
						<div class="col-3 th">
							<colored :value="page['production'][res]['total'] * 24 * 7 * 30"></colored>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="block">
			<div class="title text-center">
				Статус хранилища
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row" v-for="res in page['resources']">
						<div class="col-2 th">
							{{ $t('RESOURCES.'+res) }}
						</div>
						<div class="col-1 th">
							{{ page['production'][res]['storage'] }}%
						</div>
						<div class="col-9 th">
							<ResourcesBar :value="Math.min(100, Math.max(0, page['production'][res]['storage']))"></ResourcesBar>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div v-if="page['buy_form']['visible']" class="block">
			<div class="title text-center">
				Покупка ресурсов (8 ч. выработка ресурсов)
			</div>
			<div class="content border-0">
				<div class="table">
					<div class="row">
						<div class="col-4 th">
							<span v-if="!page['buy_form']['time']">
								<a @click.prevent="buyResources" class="button">Купить за 10 кредитов</a>
							</span>
							<span v-else>
								Следующая покупка через
								<br>
								{{ page['buy_form']['time']|time }}
							</span>
						</div>
						<div class="col-8 th middle">
							<div>
								Вы можете купить:
								<colored :value="page['buy_form']['metal']"></colored> металла,
								<colored :value="page['buy_form']['crystal']"></colored> кристалла,
								<colored :value="page['buy_form']['deuterium']"></colored> дейтерия
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import ResourcesBar from '~/components/page/resources/bar.vue'
	import ResourcesRow from '~/components/page/resources/row.vue'

	export default {
		name: 'resources',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		components: {
			ResourcesBar,
			ResourcesRow,
		},
		methods: {
			buyResources ()
			{
				this.$dialog
					.confirm({
						body: 'Купить ресурсы за 10 кредитов?',
					}, {
						okText: 'Да',
						cancelText: 'Нет',
					})
					.then(() =>
					{
						this.$get('/resources/', {
							buy: 'Y'
						})
						.then((result) => {
							this.$store.commit('PAGE_LOAD', result)
						})
					})
			}
		}
	}
</script>