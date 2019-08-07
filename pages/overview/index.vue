<template>
	<div v-if="page && $store.state['user']" class="page-overview">
		<div v-if="page['bonus']" class="block page-overview-bonus">
			<div class="title text-center">
				Ежедневный бонус
			</div>
			<div class="table">
				<div class="row">
					<div class="col th">
						Сейчас вы можете получить по <b class="positive">{{ page['bonus_count'] | number }}</b> Металла, Кристаллов и Дейтерия.<br>
						Каждый день размер бонуса будет увеличиваться.<br>
						<br>
						<button @click.prevent="getDailyBonus" class="button">Получить ресурсы</button><br>
					</div>
				</div>
			</div>
		</div>

		<div class="block">
			<div class="title">
				<div class="row">
					<div class="col-12 col-sm-6">
						{{ page['planet']['type'] }} "{{ page['planet']['name'] }}"
						<nuxt-link :to="'/galaxy/?galaxy='+page['planet']['galaxy']+'&system='+page['planet']['system']">[{{ page['planet']['galaxy'] }}:{{ page['planet']['system'] }}:{{ page['planet']['planet'] }}]</nuxt-link>
						<nuxt-link to="/overview/rename/" title="Редактирование планеты">(изменить)</nuxt-link>
					</div>
					<div class="separator d-sm-none"></div>
					<div class="col-12 col-sm-6">
						<div class="float-sm-right"><clock></clock></div>
						<div class="clearfix d-sm-none"></div>
					</div>
				</div>
			</div>
			<div class="content">

				<div v-if="page['error']" class="row">
					<div class="col text-center" v-html="page['error']"></div>
					<div class="separator"></div>
				</div>

				<div v-if="page['fleets']['length']">
					<fleets :items="page['fleets']"></fleets>
					<div class="separator"></div>
				</div>
				<div class="row overview">
					<div class="col-sm-4 col-12">
						<div class="row">
							<div class="col-12">
								<div class="planet-image">
									<nuxt-link to="/overview/rename/">
										<img :src="'/images/planeten/'+page['planet']['image']+'.jpg'" alt="">
									</nuxt-link>
									<div v-if="page['moon']" class="moon-image">
										<nuxt-link :to="'/overview/?chpl='+page['moon']['id']" :title="page['moon']['name']">
											<img :src="'/images/planeten/'+page['moon']['image']+'.jpg'" height="50" width="50" alt="">
										</nuxt-link>
									</div>
								</div>

								<div class="separator"></div>

								<div style="border: 1px solid rgb(153, 153, 255); width: 100%; margin: 0 auto;">
									<div id="CaseBarre" :style="'background-color: #'+(page['case_pourcentage'] > 80 ? 'C00000' : (page['case_pourcentage'] > 60 ? 'C0C000' : '00C000'))+'; width: '+page['case_pourcentage']+'%;  margin: 0 auto; text-align:center;'">
										<font color="#000000"><b>{{ page['case_pourcentage'] }}%</b></font>
									</div>
								</div>

								<div v-if="page['noob']">
									<div class="separator"></div>
									<img src="/images/warning.png" align="absmiddle" alt="">
									<span style="font-weight:normal;"><span class="positive">Активен режим ускорения новичков.</span><br>Режим будет деактивирован после достижения 1000 очков.</span>
								</div>
							</div>
							<div class="col-12 page-overview-officiers">
								<div v-for="item in page['officiers']" class="page-overview-officiers-item">
									<nuxt-link to="/officier/">
										<Popper>
											{{ item['name'] }}
											<br>
											<span v-if="item['time'] > $store.getters.getServerTime()">
												Нанят до <font color="lime">{{ item['time'] | date('d.m.Y H:i') }}</font>
											</span>
											<font v-else="" color="lime">Не нанят</font>
											<template slot="reference">
												<span class="officier" :class="['of'+item['id']+(item['time'] > $store.getters.getServerTime() ? '_ikon' : '')]"></span>
											</template>
										</Popper>
									</nuxt-link>
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-4 col-12">
						<div class="separator d-sm-none"></div>
						<div class="table container-fluid">
							<div class="row">
								<div class="col-12 c">Диаметр</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									{{ page['planet']['diameter'] | number }} км
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">Занятость</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									<a title="Занятость полей">{{ page['planet']['field_used'] }}</a> / <a title="Максимальное количество полей">{{ page['planet']['field_max'] }}</a> поля
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">Температура</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									от. {{ page['planet']['temp_min'] }}&deg;C до {{ page['planet']['temp_max'] }}&deg;C
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">
									Обломки
									<a v-if="page['debris_mission']" @click.prevent="sendRecycle">
										(переработать)
									</a>
								</div>
							</div>
							<div class="row">
								<div class="col-12 th doubleth middle">
									<div>
										<img src="/images/skin/s_metal.png" alt="" align="absmiddle" v-tooltip="'Металл'">
										{{ page['debris']['metal']|number }}
										/
										<img src="/images/skin/s_crystal.png" alt="" align="absmiddle" v-tooltip="'Кристалл'">
										{{ page['debris']['crystal']|number }}
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">Бои</div>
							</div>
							<div class="row">
								<div class="col-12 th middle">
									<img :src="'/images/wins.gif'" alt="" align="absmiddle" v-tooltip="'Победы'">&nbsp;
									{{ page['raids']['win'] }}
									&nbsp;&nbsp;
									<img :src="'/images/losses.gif'" alt="" align="absmiddle" v-tooltip="'Поражения'">&nbsp;
									{{ page['raids']['lost'] }}
								</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									Фракция: <nuxt-link to="/race/">{{ $t('RACES.'+$store.state['user']['race']) }}</nuxt-link>
								</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									<nuxt-link to="/refers/">
										https://{{ $store.state['host'] }}/?{{ $store.state['user']['id'] }}
									</nuxt-link>
									[{{ page['links'] }}]
								</div>
							</div>
						</div>
					</div>
					<div class="col-sm-4 col-12">
						<div class="separator d-sm-none"></div>
						<div class="table container-fluid">
							<div class="row">
								<div class="c col-sm-5 col-6">Игрок:</div>
								<div class="c col-sm-7 col-6" style="word-break: break-all;">
									<popup-link :to="'/players/'+$store.state['user']['id']+'/'">{{ $store.state['user']['name'] }}</popup-link>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Постройки:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ page['points']['build'] | number }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Флот:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ page['points']['fleet'] | number }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Оборона:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ page['points']['defs'] | number }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Наука:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ page['points']['tech'] | number }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Всего:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ page['points']['total'] | number }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Место:</div>
								<div class="th col-sm-7 col-6">
									<nuxt-link :to="'/stat/?view=players&range='+page['points']['place']">{{ page['points']['place'] }}</nuxt-link>
									<span title="Изменение места в рейтинге">
										<span v-if="page['points']['diff'] >= 1" class="positive">+{{ page['points']['diff'] }}</span>
										<span v-else-if="page['points']['diff'] < 0" class="negative">{{ page['points']['diff'] }}</span>
									</span>
								</div>
							</div>
							<div class="row">
								<div class="c col-12">Промышленный уровень</div>
							</div>
							<div class="row">
								<div class="th col-12">
									{{ page['lvl']['mine']['l'] }} из 100
								</div>
							</div>
							<div class="row">
								<div class="th col-12">
									{{ page['lvl']['mine']['p'] | number }} / {{ page['lvl']['mine']['u'] | number }} exp
								</div>
							</div>
							<div class="row">
								<div class="c col-12">Военный уровень</div>
							</div>
							<div class="row">
								<div class="th col-12">
									{{ page['lvl']['raid']['l'] }} из 100
								</div>
							</div>
							<div class="row">
								<div class="th col-12">
									{{ page['lvl']['raid']['p'] | number }} / {{ page['lvl']['raid']['u'] | number }} exp
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="clearfix"></div>

				<div v-if="page['build_list'].length > 0">
					<div class="separator"></div>
					<div class="table">
						<queue-row v-for="(list, i) in page['build_list']" :key="i" :item="list"></queue-row>
					</div>
				</div>
			</div>
		</div>

		<div v-if="page['chat'].length > 0" class="page-overview-chat">
			<div class="separator"></div>

			<table class="table" style="max-width: 100%">
				<tbody>
					<tr>
						<th class="text-left">
							<div style="overflow-y: auto;overflow-x: hidden;">
								<div v-for="item in page['chat']" class="activity">
									<div class="date1" style="display: inline-block;padding-right:5px;">{{ item.time | date('H:i') }}</div>
									<div style="display: inline;white-space:pre-wrap" v-html="item.message"></div>
								</div>
							</div>
						</th>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>

<script>
	import Fleets from '~/components/page/overview/fleets.vue'
	import Clock from '~/components/page/overview/clock.vue'
	import QueueRow from '~/components/page/overview/queue-row.vue'
	import { sendMission } from '~/utils/fleet'

	export default {
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		components: {
			Fleets,
			Clock,
			QueueRow,
		},
		methods: {
			sendRecycle () {
				sendMission(this, 8, this.page['planet']['galaxy'], this.page['planet']['system'], this.page['planet']['planet'], 2)
			},
			async getDailyBonus ()
			{
				const result = await this.$post('/overview/', {
					bonus: 'Y'
				})

				this.$store.commit('PAGE_LOAD', result);
			}
		}
	}
</script>