<template>
	<div class="page-overview">
		<div v-if="page['bonus']" class="page-overview-bonus">
			<table class="table">
				<tr>
					<td class="c">Ежедневный бонус</td>
				</tr>
				<tr>
					<th>
						Сейчас вы можете получить по <b class="positive">{{ Format.number(page['bonus_count']) }}</b> Металла, Кристаллов и Дейтерия.<br>
						Каждый день размер бонуса будет увеличиваться.<br>
						<br>
						<a :href="$root.getUrl('overview/bonus/')" class="button">Получить ресурсы</a><br>
					</th>
				</tr>
			</table>
			<div class="separator"></div>
		</div>

		<div class="block">
			<div class="title">
				<div class="row">
					<div class="col-12 col-sm-6">
						{{ page['planet']['type'] }} "{{ page['planet']['name'] }}"
						<a :href="$root.getUrl('galaxy/'+page['planet']['galaxy']+'/'+page['planet']['system']+'/')">[{{ page['planet']['galaxy'] }}:{{ page['planet']['system'] }}:{{ page['planet']['planet'] }}]</a>
						<a :href="$root.getUrl('overview/rename/')" title="Редактирование планеты">(изменить)</a>
					</div>
					<div class="separator d-sm-none"></div>
					<div class="col-12 col-sm-6">
						<div class="float-sm-right">{{ date("d.m.Y H:i:s", clock) }}</div>
						<div class="clearfix d-sm-none"></div>
					</div>
				</div>
			</div>
			<div class="content">
				<div v-if="page['fleets']['length']">
					<game-page-overview-fleets :items="page['fleets']"></game-page-overview-fleets>
					<div class="separator"></div>
				</div>
				<div class="row overview">
					<div class="col-sm-4 col-12">
						<div class="row">
							<div class="col-12">
								<div class="planet-image">
									<a :href="$root.getUrl('overview/rename/')">
										<img :src="$root.getUrl('assets/images/planeten/'+page['planet']['image']+'.jpg')" alt="">
									</a>
									<div v-if="page['moon']" class="moon-image">
										<a :href="$root.getUrl('overview/?chpl='+page['moon']['id'])" :title="page['moon']['name']">
											<img :src="$root.getUrl('assets/images/planeten/'+page['moon']['image']+'.jpg')" height="50" width="50">
										</a>
									</div>
								</div>

								<div class="separator"></div>

								<div style="border: 1px solid rgb(153, 153, 255); width: 100%; margin: 0 auto;">
									<div id="CaseBarre" :style="'background-color: #'+(page['case_pourcentage'] > 80 ? 'C00000' : (page['case_pourcentage'] > 60 ? 'C0C000' : '00C000'))+'; width: '+page['case_pourcentage']+'%;  margin: 0 auto; text-align:center;'">
										<font color="#000000"><b>{{ page['case_pourcentage'] }}%</b></font></div>
								</div>

								<div v-if="page['noob']">
									<div class="separator"></div>
									<img :src="$root.getUrl('assets/images/warning.png')" align="absmiddle" alt="">
									<span style="font-weight:normal;"><span class="positive">Активен режим ускорения новичков.</span><br>Режим будет деактивирован после достижения 1000 очков.</span>
								</div>
							</div>
							<div class="col-12 page-overview-officiers">
								<div v-for="item in page['officiers']" class="page-overview-officiers-item">
									<a :href="$root.getUrl('officier/')" class="tooltip">
										<div class="tooltip-content">
											{{ item['name'] }}
											<br>
											<span v-if="item['time'] > $root.serverTime()">
												Нанят до <font color="lime">{{ date("d.m.Y H:i", item['time']) }}</font>
											</span>
											<font v-else="" color="lime">Не нанят</font>
										</div>
										<span class="officier" :class="['of'+item['id']+(item['time'] > $root.serverTime() ? '_ikon' : '')]"></span>
									</a>
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
									{{ Format.number(page['planet']['diameter']) }} км
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
									<a v-if="page['debris_mission']" v-on:click.prevent="fleet.sendMission(8, page['planet']['galaxy'], page['planet']['system'], page['planet']['planet'], 2)">
										(переработать)
									</a>
								</div>
							</div>
							<div class="row">
								<div class="col-12 th doubleth middle">
									<div>
										<img :src="$root.getUrl('assets/images/skin/s_metal.png')" alt="" align="absmiddle" class="tooltip" data-content="Металл">
										{{ Format.number(page['debris']['metal']) }}
										/
										<img :src="$root.getUrl('assets/images/skin/s_crystal.png')" alt="" align="absmiddle" class="tooltip" data-content="Кристалл">
										{{ Format.number(page['debris']['crystal']) }}
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">Бои</div>
							</div>
							<div class="row">
								<div class="col-12 th middle">
									<img :src="$root.getUrl('assets/images/wins.gif')" alt="" align="absmiddle" class="tooltip" data-content="Победы">&nbsp;
									{{ page['raids']['win'] }}
									&nbsp;&nbsp;
									<img :src="$root.getUrl('assets/images/losses.gif')" alt="" align="absmiddle" class="tooltip" data-content="Поражения">&nbsp;
									{{ page['raids']['lost'] }}
								</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									Фракция: <a :href="$root.getUrl('race/')">{{ $root.getLang('RACES', $store.state['user']['race']) }}</a>
								</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									<a :href="$root.getUrl('refers/')">https://{{ $store.state['host'] }}/?{{ $store.state['user']['id'] }}</a> [{{ page['links'] }}]
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
									<a :href="$root.getUrl('players/'+$store.state['user']['id']+'/')" class="window popup-user">{{ $store.state['user']['name'] }}</a>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Постройки:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page['points']['build']) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Флот:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page['points']['fleet']) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Оборона:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page['points']['defs']) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Наука:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page['points']['tech']) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Всего:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page['points']['total']) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Место:</div>
								<div class="th col-sm-7 col-6">
									<a :href="$root.getUrl('stat/players/range/'+page['points']['place']+'/')">{{ page['points']['place'] }}</a>
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
									{{ Format.number(page['lvl']['mine']['p']) }} / {{ Format.number(page['lvl']['mine']['u']) }} exp
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
									{{ Format.number(page['lvl']['raid']['p']) }} / {{ Format.number(page['lvl']['raid']['u']) }} exp
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div v-if="page['chat'].length > 0" class="page-overview-chat d-sm-none">
			<div class="separator"></div>

			<table class="table" style="max-width: 100%">
				<tr>
					<th class="text-left">
						<div style="overflow-y: auto;overflow-x: hidden;">
							<div v-for="item in page['chat']" class="activity">
								<div class="date1" style="display: inline-block;padding-right:5px;">{{ date("H:i", item.time) }}</div>
								<div style="display: inline;white-space:pre-wrap" v-html="item.message"></div>
							</div>
						</div>
					</th>
				</tr>
			</table>
		</div>
	</div>
</template>

<script>
	export default {
		name: "overview",
		components: {
			'game-page-overview-fleets': require('./overview-fleets.vue'),
		},
		computed: {
			page () {
				return this.$store.state.page;
			}
		},
		data ()
		{
			return {
				clock: 0,
				clock_timeout: null,
				fleet: require('./../../js/fleet.js')
			}
		},
		methods: {
			clockUpdate () {
				this.clock = this.$root.serverTime();
			},
			clockStop () {
				clearTimeout(this.clock_timeout);
			},
			clockStart () {
				this.clock_timeout = setTimeout(this.clockUpdate, 1000);
			}
		},
		watch: {
			clock () {
				this.clockStart();
			}
		},
		mounted ()
		{
			this.clockStop();
			this.clockUpdate();
		},
		destroyed () {
			this.clockStop();
		}
	}
</script>