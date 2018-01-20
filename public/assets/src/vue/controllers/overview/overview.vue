<template>
	<div>
		<div class="block">
			<div class="title">
				<div class="row">
					<div class="col-12 col-sm-6">
						{{ page.planet_type }} "{{ page.planet_name }}"
						<a v-bind:href="$root.getUrl('galaxy/'+page.planet_galaxy+'/'+page.planet_system+'/')">[{{ page.planet_galaxy }}:{{ page.planet_system }}:{{ page.planet_planet }}]</a>
						<a v-bind:href="$root.getUrl('overview/rename/')" title="Редактирование планеты">(изменить)</a>
					</div>
					<div class="separator d-sm-none"></div>
					<div class="col-12 col-sm-6">
						<div class="float-right">{{ date("d-m-Y H:i:s", clock) }}</div>
						<div class="clearfix d-sm-none"></div>
					</div>
				</div>
			</div>
			<div class="content">
				<div v-if="page.fleets">
					<game-page-overview-fleets v-bind:items="page.fleets"></game-page-overview-fleets>
					<div class="separator"></div>
				</div>
				<div class="row overview">
					<div class="col-sm-4 col-12">
						<div class="row">
							<div class="col-md-10 col-sm-12 col-5">
								<div class="planet-image">
									<a v-bind:href="$root.getUrl('overview/rename/')">
										<img v-bind:src="$root.getUrl('assets/images/planeten/'+page.planet_image+'.jpg')" alt="">
									</a>
									<div v-if="page.moon" class="moon-image">
										<a v-bind:href="$root.getUrl('overview/?chpl='+page.moon.id)" v-bind:title="page.moon.name">
											<img v-bind:src="$root.getUrl('assets/images/planeten/'+page.moon.image+'.jpg')" height="50" width="50">
										</a>
									</div>
								</div>

								<div class="separator"></div>

								<div style="border: 1px solid rgb(153, 153, 255); width: 100%; margin: 0 auto;">
									<div id="CaseBarre" v-bind:style="'background-color: #'+(page.case_pourcentage > 80 ? 'C00000' : (page.case_pourcentage > 60 ? 'C0C000' : '00C000'))+'; width: '+page.case_pourcentage+'%;  margin: 0 auto; text-align:center;'">
										<font color="#000000"><b>{{ page.case_pourcentage }}%</b></font></div>
								</div>

								<div v-if="page.noob">
									<div class="separator"></div>
									<img v-bind:src="$root.getUrl('assets/images/warning.png')" align="absmiddle" alt="">
									<span style="font-weight:normal;"><span class="positive">Активен режим ускорения новичков.</span><br>Режим будет деактивирован после достижения 1000 очков.</span>
								</div>
							</div>
							<div class="col-md-2 col-sm-12 col-7">
								<div class="row">
									<div v-for="item in page.officiers" class="col-3 col-sm-2 col-md-12">
										<a v-bind:href="$root.getUrl('officier/')" class="tooltip">
											<div class="tooltip-content">
												{{ item.name }}
												<br>
												<span v-if="item.time > ((new Date()).getTime() / 1000)">
													Нанят до <font color="lime">{{ date("d.m.Y H:i", item.time) }}</font>
												</span>
												<font v-else color="lime">Не нанят</font>
											</div>
											<span v-bind:class="['officier', 'of'+item.id+(item.time > ((new Date()).getTime() / 1000) ? '_ikon' : '')]"></span>
										</a>
									</div>
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
									{{ Format.number(page.planet_diameter) }} км
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">Занятость</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									<a title="Занятость полей">{{ page.planet_field_current }}</a> / <a title="Максимальное количество полей">{{ page.planet_field_max }}</a> поля
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">Температура</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									от. {{ page.planet_temp_min }}&deg;C до {{ page.planet_temp_max }}&deg;C
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">
									Обломки
									<a v-if="page.get_link" href="#" v-bind:onclick="QuickFleet(8, page.planet_galaxy, page.planet_system, page.planet_planet, 2)">
										(переработать)
									</a>
								</div>
							</div>
							<div class="row">
								<div class="col-12 th doubleth middle">
									<div>
										<img v-bind:src="$root.getUrl('assets/images/skin/s_metall.png')" alt="" align="absmiddle" class="tooltip" data-content="Металл">
										{{ Format.number(page.debris.metal) }}
										/
										<img v-bind:src="$root.getUrl('assets/images/skin/s_kristall.png')" alt="" align="absmiddle" class="tooltip" data-content="Кристалл">
										{{ Format.number(page.debris.crystal) }}
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-12 c">Бои</div>
							</div>
							<div class="row">
								<div class="col-12 th middle">
									<img v-bind:src="$root.getUrl('assets/images/wins.gif')" alt="" align="absmiddle" class="tooltip" data-content="Победы">
									{{ page.raids_win }}
									&nbsp;&nbsp;
									<img v-bind:src="$root.getUrl('assets/images/losses.gif')" alt="" align="absmiddle" class="tooltip" data-content="Поражения">
									{{ page.raids_lose }}
								</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									Фракция: <a v-bind:href="$root.getUrl('race/')">{{ $root.user.race }}</a>
								</div>
							</div>
							<div class="row">
								<div class="col-12 th">
									<a v-bind:href="$root.getUrl('refers/')">https://{{ $root.host }}/?{{ $root.user.id }}</a> [{{ page.links }}]
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
									<a v-bind:href="$root.getUrl('players/'+$root.user.id+'/')" class="window popup-user">{{ $root.user.name }}</a>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Постройки:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page.user_points) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Флот:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page.user_fleet) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Оборона:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page.user_defs) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Наука:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page.player_points_tech) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Всего:</div>
								<div class="th col-sm-7 col-6">
									<span class="positive">{{ Format.number(page.total_points) }}</span>
								</div>
							</div>
							<div class="row">
								<div class="th col-sm-5 col-6">Место:</div>
								<div class="th col-sm-7 col-6">
									<a v-bind:href="$root.getUrl('stat/players/range/'+page.user_rank+'/')">{{ page.user_rank }}</a>
									<span title="Изменение места в рейтинге">
										<span v-if="page.ile >= 1" class="positive">+{{ page.ile }}</span>
										<span v-else-if="page.ile < 0" class="negative">{{ page.ile }}</span>
										<font v-else color="lightblue">~</font>
									</span>
								</div>
							</div>
							<div class="row">
								<div class="c col-12">Промышленный уровень</div>
							</div>
							<div class="row">
								<div class="th col-12">
									{{ page.lvl.mine.l }} из 100
								</div>
							</div>
							<div class="row">
								<div class="th col-12">
									{{ Format.number(page.lvl.mine.p) }} / {{ Format.number(page.lvl.mine.u) }} exp
								</div>
							</div>
							<div class="row">
								<div class="c col-12">Военный уровень</div>
							</div>
							<div class="row">
								<div class="th col-12">
									{{ page.lvl.raid.l }} из 100
								</div>
							</div>
							<div class="row">
								<div class="th col-12">
									{{ Format.number(page.lvl.raid.p) }} / {{ Format.number(page.lvl.raid.u) }} exp
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div v-if="page.chat">
			<div class="separator"></div>

			<table class="table" style="max-width: 100%">
				<tr>
					<th class="text-left">
						<div style="overflow-y: auto;overflow-x: hidden;">
							<div v-for="item in page.chat">
								<div class="activity">
									<div class="date1" style="display: inline-block;padding-right:5px;">{{ date("H:i", item.time) }}</div>
									<div style="display: inline;white-space:pre-wrap" v-html="item.message"></div>
								</div>
								<div class="clear"></div>
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
		props: ['page'],
		data: function()
		{
			return {
				clock: 0
			}
		},
		methods: {
			updateClock: function()
			{
				this.clock = Math.floor((new Date).getTime() / 1000);
			},
			stopClock: function()
			{
				clearTimeout(timeouts['overview_clock']);
			},
			startClock: function ()
			{
				timeouts['overview_clock'] = setTimeout(this.updateClock, 1000);
			}
		},
		watch: {
			clock: function()
			{
				this.startClock();
			}
		},
		mounted: function()
		{
			this.stopClock();
			this.updateClock();
		},
		destroyed: function ()
		{
			this.stopClock();
		},
		components: {
			'game-page-overview-fleets': require('./overview-fleets.vue'),
		},
	}
</script>

<style scoped>

</style>