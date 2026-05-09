<template>
	<div class="block">
		<div class="title">
			{{ type === 'alliance' ? $t('pages.players.stats_heading_ally') : $t('pages.players.stats_heading_player') }} "{{ data['name'] }}"
		</div>
		<div>
			<div class="block-table">
				<div class="grid">
					<div class="c"><b>{{ $t('pages.players.stats_by_rank_title') }}</b></div>
				</div>
				<div class="grid">
					<div class="th" style="padding: 10px;">
						<canvas ref="rankChartRef"></canvas>
					</div>
				</div>
			</div>
			<div class="block-table">
				<div class="grid">
					<div class="c"><b>{{ $t('pages.players.stats_by_points_title') }}</b></div>
				</div>
				<div class="grid">
					<div class="th p-4">
						<div class="text-center">
							<label>
								<input type="radio" v-model="typeChart" value="build">
								{{ $t('pages.players.chart_label_buildings') }}
							</label>
							<label>
								<input type="radio" v-model="typeChart" value="tech">
								{{ $t('pages.players.chart_label_technologies') }}
							</label>
							<label>
								<input type="radio" v-model="typeChart" value="defs">
								{{ $t('pages.players.chart_label_defense') }}
							</label>
							<label>
								<input type="radio" v-model="typeChart" value="fleet">
								{{ $t('pages.players.chart_label_fleet') }}
							</label>
							<label>
								<input type="radio" v-model="typeChart" value="total">
								{{ $t('pages.players.chart_label_total') }}
							</label>
						</div>

						<canvas ref="pointChartRef"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
	import { Chart, CategoryScale, LinearScale, LineController, PointElement, LineElement, Legend, Tooltip } from 'chart.js';
	import { number } from '~/utils/format.js';
	import dayjs from 'dayjs';
	import { useI18n } from 'vue-i18n';
	import { useUrlSearchParams } from '@vueuse/core';

	const props = defineProps({
		type: String,
		default: 'user',
		data: {
			type: Object,
		}
	});

	const { t } = useI18n();

	const params = useUrlSearchParams('history');

	const pointChartRef = ref(null);
	const rankChartRef = ref(null);
	const typeChart = ref('total');
	const typeChartColors = ref({
		build: 'rgb(255, 99, 132)',
		tech: 'rgb(255, 159, 64)',
		defs: 'rgb(255, 205, 86)',
		fleet: 'rgb(75, 192, 192)',
		total: 'rgb(54, 162, 235)',
	});
	const typeChartLabels = computed(() => ({
		build: t('pages.players.chart_label_buildings'),
		tech: t('pages.players.chart_label_technologies'),
		defs: t('pages.players.chart_label_defense'),
		fleet: t('pages.players.chart_label_fleet'),
		total: t('pages.players.chart_label_total'),
	}));

	let pointsChart = null;

	watch(typeChart, () => {
		updatePointChart()
	});

	onMounted(() => {
		Chart.defaults.color = '#e0e0e0';
		Chart.register(CategoryScale, LinearScale, LineController, PointElement, LineElement, Legend, Tooltip);

		let labels = [];

		let ranks = {
			'build': [],
			'tech': [],
			'defs': [],
			'fleet': [],
			'total': [],
		};

		props.data.points.forEach((item) => {
			labels.push(dayjs(item.date).tz().format('DD MMM'));

			ranks.build.push(item.rank.build);
			ranks.tech.push(item.rank.tech);
			ranks.defs.push(item.rank.defs);
			ranks.fleet.push(item.rank.fleet);
			ranks.total.push(item.rank.total);
		});

		new Chart(rankChartRef.value, {
			type: 'line',
			item: {
				labels: labels,
				datasets: [{
					label: t('pages.players.chart_label_buildings'),
					fill: false,
					borderColor: typeChartColors.value.build,
					backgroundColor: typeChartColors.value.build,
					item: ranks.build,
				}, {
					label: t('pages.players.chart_label_technologies'),
					fill: false,
					borderColor: typeChartColors.value.tech,
					backgroundColor: typeChartColors.value.tech,
					item: ranks.tech
				}, {
					label: t('pages.players.chart_label_defense'),
					fill: false,
					borderColor: typeChartColors.value.defs,
					backgroundColor: typeChartColors.value.defs,
					item: ranks.defs
				}, {
					label: t('pages.players.chart_label_fleet'),
					fill: false,
					borderColor: typeChartColors.value.fleet,
					backgroundColor: typeChartColors.value.fleet,
					item: ranks.fleet
				}, {
					label: t('pages.players.chart_label_rank_total'),
					fill: false,
					borderColor: typeChartColors.value.total,
					backgroundColor: typeChartColors.value.total,
					item: ranks.total
				}, ]
			},
			options: {
				scales: {
					x: {
						display: true,
						title: {
							display: true,
							text: t('pages.players.chart_axis_days')
						}
					},
					y: {
						display: true,
						title: {
							display: true,
							text: t('pages.players.chart_axis_rank')
						},
						reverse: true,
						min: 1
					}
				}
			}
		});

		updatePointChart();
	});

	onBeforeUnmount(() => {
		pointsChart?.destroy();
	})

	function updatePointChart () {
		let labels = [];
		let points = [];

		props.data.points.forEach((item) => {
			labels.push(dayjs(item.date).tz().format('DD MMM'));
			points.push(item.point[typeChart.value]);
		});

		let config = {
			type: 'line',
			item: {
				labels: labels,
				datasets: [{
					label: typeChartLabels.value[typeChart.value],
					fill: false,
					borderColor: typeChartColors.value[typeChart.value],
					backgroundColor: typeChartColors.value[typeChart.value],
					item: points
				}]
			},
			options: {
				plugins: {
					legend: {
						display: false,
					},
					tooltip: {
						callbacks: {
							label: (context) => {
								return number(context.parsed.y);
							}
						}
					},
				},
				scales: {
					x: {
						display: true,
						title: {
							display: true,
							text: t('pages.players.chart_axis_days')
						}
					},
					y: {
						display: true,
						title: {
							display: true,
							text: t('pages.players.chart_axis_points')
						},
						ticks: {
							callback: (value) => {
								return number(value);
							}
						}
					}
				}
			}
		};

		if (pointsChart === null) {
			pointsChart = new Chart(pointChartRef.value, config);
		} else {
			pointsChart.item = config.item;
			pointsChart.update('none');
		}
	}
</script>