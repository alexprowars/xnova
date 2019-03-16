<template>
	<div v-if="page" class="page-galaxy">
		<galaxy-selector :shortcuts="page['shortcuts']"></galaxy-selector>
		<div class="separator"></div>

		<router-form v-if="missile" :action="'/rocket/?c='+$store.state['user']['planet']+'&mode=2&galaxy='+page['galaxy']+'&system='+page['system']+'&planet='+missilePlanet">
			<table border="0" class="table">
				<tbody>
				<tr>
					<td class="c" colspan="3">
						Начать ракетную атаку на [{{ page['galaxy'] }}:{{ page['system'] }}:{{ missilePlanet }}]
					</td>
				</tr>
				<tr>
					<td class="th">
						Количество ракет (<b>{{ page['user']['interplanetary_misil'] }}</b>):
						<input type="number" name="count" style="width:25%" min="1" :max="page['user']['interplanetary_misil']" :value="page['user']['interplanetary_misil']">
					</td>
					<td class="th">
						Цель:
						<select name="target">
							<option value="all" selected>Вся оборона</option>
							<option value="0">{{ $t('TECH.401') }}</option>
							<option value="1">{{ $t('TECH.402') }}</option>
							<option value="2">{{ $t('TECH.403') }}</option>
							<option value="3">{{ $t('TECH.404') }}</option>
							<option value="4">{{ $t('TECH.405') }}</option>
							<option value="5">{{ $t('TECH.406') }}</option>
							<option value="6">{{ $t('TECH.407') }}</option>
							<option value="7">{{ $t('TECH.408') }}</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="c" colspan="2">
						<input type="submit" name="send" value="Отправить">
						<input type="button" value="Отмена" @click.prevent="missile = false">
					</td>
				</tr>
				</tbody>
			</table>
			<div class="separator"></div>
		</router-form>

		<div class="table-responsive">
			<table class="table galaxy">
				<tbody>
				<tr>
					<td class="c" colspan="9">Солнечная система {{ page['galaxy'] }}:{{ page['system'] }}</td>
				</tr>
				<tr>
					<td class="c">№</td>
					<td class="c">&nbsp;</td>
					<td class="c">Планета</td>
					<td class="c">&nbsp;</td>
					<td class="c">ПО</td>
					<td class="c">Игрок</td>
					<td class="c">&nbsp;</td>
					<td class="c">Альянс</td>
					<td class="c">Действия</td>
				</tr>

				<tr is="galaxy-row" v-for="(item, index) in page['items']" :item="item" :key="index" :i="index"></tr>

				<tr v-if="page['user']['allowExpedition']">
					<th width="30">16</th>
					<th colspan="8" class="c big">
						<nuxt-link :to="'/fleet/g'+page['galaxy']+'/s'+page['system']+'/p16/t0/m15/'">неизведанные дали</nuxt-link>
					</th>
				</tr>
				<tr>
					<td class="c" colspan="6">
						<span v-if="planets === 1">{{ planets }} заселённая планета</span>
						<span v-else-if="planets === 1">нет заселённых планет</span>
						<span v-else>{{ planets }} заселённые планеты</span>
					</td>
					<td class="c" colspan=3>
						<v-popover>
							<template slot="popover">
								<table width="240">
									<tbody>
									<tr>
										<td width="220">Сильный игрок</td>
										<td><span class="strong">S</span></td>
									</tr>
									<tr>
										<td>Слабый игрок</td>
										<td><span class="noob">N</span></td>
									</tr>
									<tr>
										<td>Режим отпуска</td>
										<td><span class="vacation">U</span></td>
									</tr>
									<tr>
										<td>Заблокирован</td>
										<td><span class="banned">G</span></td>
									</tr>
									<tr>
										<td>Неактивен 7 дней</td>
										<td><span class="inactive">i</span></td>
									</tr>
									<tr>
										<td>Неактивен 28 дней</td>
										<td><span class="longinactive">iI</span></td>
									</tr>
									<tr>
										<td><font color="red">Администратор</font></td>
										<td><font color="red">A</font></td>
									</tr>
									<tr>
										<td><font color="green">Оператор</font></td>
										<td><font color="green">GO</font></td>
									</tr>
									<tr>
										<td><font color="orange">Супер оператор</font></td>
										<td><font color="orange">SGO</font></td>
									</tr>
									</tbody>
								</table>
							</template>
							Легенда
						</v-popover>
					</td>
				</tr>
				<tr>
					<td class="c" colspan="3"><span id="missiles">{{ page['user']['interplanetary_misil'] }}</span> межпланетных ракет</td>
					<td class="c" colspan="3"><span id="slots">{{ page['user']['fleets'] }}</span>/{{ page['user']['max_fleets'] }} флотов</td>
					<td class="c" colspan="3">
						<span id="recyclers">{{ page['user']['recycler']|number }}</span> переработчиков<br>
						<span id="probes">{{ page['user']['spy_sonde']|number }}</span> шпионских зондов
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
	import GalaxyRow from '~/components/page/galaxy/row.vue'
	import GalaxySelector from '~/components/page/galaxy/selector.vue'

	export default {
		name: "galaxy",
		components: {
			GalaxyRow,
			GalaxySelector
		},
		asyncData ({ store, route, error })
		{
			return store.dispatch('loadPage', route.fullPath)
				.catch((e) => {
					error({statusCode: '404', message: e.message})
				})
		},
		watchQuery: true,
		middleware: ['auth'],
		data () {
			return {
				missile: false,
				missilePlanet: 0,
			}
		},
		computed: {
			planets ()
			{
				if (!this.page.items)
					return 0;

				let count = 0;

				this.page.items.forEach((item) =>
				{
					if (item !== false)
						count++;
				});

				return count;
			}
		},
		methods: {
			sendMissile (planet)
			{
				this.missile = true;
				this.missilePlanet = planet;
			}
		}
	}
</script>