<template>
	<div class="block">
		<div class="title">
			{{ pageType === 'ally' ? 'Альянс' : 'Игрок' }} "{{ page['name'] }}"
		</div>
		<div class="content border-0">
			<div class="table">
				<div class="row">
					<div class="col c"><b>Статистика по месту</b></div>
				</div>
				<div class="row">
					<div class="col th" style="padding: 10px;">
						<canvas ref="rank_chart"></canvas>
					</div>
				</div>
				<div class="row">
					<div class="col c"><b>Статистика по очкам</b></div>
				</div>
				<div class="row">
					<div class="col th" style="padding: 10px;">

						<input type="radio" id="show_builds" v-model="typeChart" value="build">
						<label for="show_builds">Постройки</label>

						<input type="radio" id="show_tech" v-model="typeChart" value="tech">
						<label for="show_tech">Технологии</label>

						<input type="radio" id="show_defs" v-model="typeChart" value="defs">
						<label for="show_defs">Оборона</label>

						<input type="radio" id="show_fleet" v-model="typeChart" value="fleet">
						<label for="show_fleet">Флот</label>

						<input type="radio" id="show_total" v-model="typeChart" value="total">
						<label for="show_total">Всего</label>

						<canvas ref="point_chart"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import Chart from 'chart.js'

	export default {
		name: 'players_stat',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		data () {
			return {
				pageType: typeof this.$route.params['ally_id'] !== 'undefined' ? 'ally' : 'user',
				typeChart: 'total',
				pointsChart: null,
				typeChartColors: {
					build: 'rgb(255, 99, 132)',
					tech: 'rgb(255, 159, 64)',
					defs: 'rgb(255, 205, 86)',
					fleet: 'rgb(75, 192, 192)',
					total: 'rgb(54, 162, 235)',
				},
				typeChartLabels: {
					build: 'Постройки',
					tech: 'Технологии',
					defs: 'Оборона',
					fleet: 'Флот',
					total: 'Всего',
				}
			}
		},
		watch: {
			typeChart () {
				this.updatePointChart()
			}
		},
		methods: {
			updatePointChart ()
			{
				let labels = [];
				let points = [];

				this.page.points.forEach((item) =>
				{
					labels.push(this.$options.filters.date(item.date, 'd.m'));
					points.push(item.point[this.typeChart]);
				});

				let config = {
					type: 'line',
					data: {
						labels: labels,
						datasets: [{
							label: this.typeChartLabels[this.typeChart],
							fill: false,
							borderColor: this.typeChartColors[this.typeChart],
							backgroundColor: this.typeChartColors[this.typeChart],
							data: points
						}]
					},
					options: {
						legend: {
							onClick: null
						},
						tooltips: {
							callbacks: {
								label: (tooltipItem) => {
									return this.$options.filters.number(tooltipItem.yLabel);
								}
							}
						},
						scales: {
							xAxes: [{
								display: true,
								scaleLabel: {
									display: true,
									labelString: 'Дни'
								}
							}],
							yAxes: [{
								display: true,
								scaleLabel: {
									display: true,
									labelString: 'Очки'
								},
								ticks: {
									callback: (value) => {
										return this.$options.filters.number(value);
									}
								}
							}]
						}
					}
				};

				if (this.pointsChart === null)
					this.pointsChart = new Chart(this.$refs['point_chart'], config);
				else
				{
					this.pointsChart.data = config.data;
					this.pointsChart.update();
				}
			},
		},
		mounted ()
		{
			Chart.defaults.global.defaultFontColor = '#e0e0e0';

			let labels = [];

			let ranks = {
				'build': [],
				'tech': [],
				'defs': [],
				'fleet': [],
				'total': [],
			};

			this.page.points.forEach((item) =>
			{
				labels.push(this.$options.filters.number(item.date, 'd.m'));

				ranks.build.push(item.rank.build);
				ranks.tech.push(item.rank.tech);
				ranks.defs.push(item.rank.defs);
				ranks.fleet.push(item.rank.fleet);
				ranks.total.push(item.rank.total);
			});

			this.$nextTick(() =>
			{
				new Chart(this.$refs['rank_chart'], {
					type: 'line',
					data: {
						labels: labels,
						datasets: [{
							label: 'Постройки',
							fill: false,
							borderColor: this.typeChartColors.build,
							backgroundColor: this.typeChartColors.build,
							data: ranks.build
						}, {
							label: 'Технологии',
							fill: false,
							borderColor: this.typeChartColors.tech,
							backgroundColor: this.typeChartColors.tech,
							data: ranks.tech
						}, {
							label: 'Оборона',
							fill: false,
							borderColor: this.typeChartColors.defs,
							backgroundColor: this.typeChartColors.defs,
							data: ranks.defs
						}, {
							label: 'Флот',
							fill: false,
							borderColor: this.typeChartColors.fleet,
							backgroundColor: this.typeChartColors.fleet,
							data: ranks.fleet
						}, {
							label: 'Место',
							fill: false,
							borderColor: this.typeChartColors.total,
							backgroundColor: this.typeChartColors.total,
							data: ranks.total
						}, ]
					},
					options: {
						scales: {
							xAxes: [{
								display: true,
								scaleLabel: {
									display: true,
									labelString: 'Дни'
								}
							}],
							yAxes: [{
								display: true,
								scaleLabel: {
									display: true,
									labelString: 'Место'
								},
								ticks: {
									reverse: true,
									min: 1
								},
							}]
						}
					}
				});

				this.updatePointChart();
			})
		}
	}
</script>