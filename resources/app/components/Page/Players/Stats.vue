<template>
	<div class="page-stat-history">
		<header class="stat-history-heading">
			<div>
				<span class="stat-history-kind">{{ type === 'alliance' ? $t('pages.players.stats_heading_ally') : $t('pages.players.stats_heading_player') }}</span>
				<h1>{{ data.name }}</h1>
			</div>
			<span v-if="data.points.length" class="stat-history-period">{{ period }}</span>
		</header>

		<template v-if="data.points.length">
			<section class="stat-history-panel">
				<header class="stat-history-panel-heading">
					<h2>{{ $t('pages.players.stats_by_rank_title') }}</h2>
					<span class="stat-history-value"><span>{{ $t('pages.players.chart_axis_rank') }}</span> {{ number(latest.rank.total) }}</span>
				</header>
				<div class="stat-history-chart">
					<canvas ref="rankChartRef" role="img" :aria-label="$t('pages.players.stats_by_rank_title')"></canvas>
				</div>
			</section>

			<section class="stat-history-panel">
				<header class="stat-history-panel-heading">
					<h2>{{ $t('pages.players.stats_by_points_title') }}</h2>
					<span class="stat-history-value"><span>{{ typeChartLabels[typeChart] }}</span> {{ number(latest.point[typeChart]) }}</span>
				</header>
				<UiTabSelect v-model="typeChart" :items="chartTabs" :label="$t('pages.players.stats_by_points_title')"/>
				<div class="stat-history-chart">
					<canvas ref="pointChartRef" role="img" :aria-label="$t('pages.players.stats_by_points_title') + ': ' + typeChartLabels[typeChart]"></canvas>
				</div>
			</section>
		</template>
		<div v-else class="stat-history-empty">{{ $t('pages.players.stats_empty') }}</div>
	</div>
</template>

<script setup>
	import { UiTabSelect } from '~/components/UI';
	import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
	import { Chart, CategoryScale, LinearScale, LineController, PointElement, LineElement, Legend, Tooltip } from 'chart.js';
	import { number } from '~/utils/format.js';
	import dayjs from 'dayjs';
	import { useI18n } from 'vue-i18n';

	const props = defineProps({
		type: { type: String, default: 'user' },
		data: Object,
	});

	const { t } = useI18n();
	const pointChartRef = ref(null);
	const rankChartRef = ref(null);
	const typeChart = ref('total');
	const typeChartColors = {
		build: '#ca9e7a',
		tech: '#b4a1d6',
		defs: '#d6c080',
		fleet: '#83bda8',
		total: '#79c5de',
	};
	const typeChartLabels = computed(() => ({
		build: t('pages.players.chart_label_buildings'),
		tech: t('pages.players.chart_label_technologies'),
		defs: t('pages.players.chart_label_defense'),
		fleet: t('pages.players.chart_label_fleet'),
		total: t('pages.players.chart_label_total'),
	}));
	const chartTabs = computed(() => Object.entries(typeChartLabels.value).map(([id, label]) => ({ id, label, color: typeChartColors[id] })));
	const latest = computed(() => props.data.points.at(-1));
	const period = computed(() => props.data.points.length ? dayjs(props.data.points[0].date).tz().format('DD MMM') + ' — ' + dayjs(latest.value.date).tz().format('DD MMM YYYY') : '');

	let pointsChart = null;
	let rankChart = null;

	function chartOptions(isRank) {
		return {
			responsive: true,
			maintainAspectRatio: false,
			color: '#9eb8c9',
			font: { size: 12 },
			interaction: { mode: 'index', intersect: false },
			elements: {
				line: { borderWidth: 2, tension: 0.15 },
				point: { radius: 2, hoverRadius: 4, hitRadius: 8 },
			},
			plugins: {
				legend: {
					display: isRank,
					position: 'bottom',
					labels: { color: '#aec5d3', usePointStyle: true, boxWidth: 8, boxHeight: 8, padding: 14, font: { size: 12 } },
				},
				tooltip: {
					backgroundColor: '#0b1926', borderColor: '#355166', borderWidth: 1,
					titleColor: '#e0edf5', bodyColor: '#bed2df', padding: 10,
					titleFont: { size: 12 }, bodyFont: { size: 12 },
					callbacks: { label: (context) => context.dataset.label + ': ' + number(context.parsed.y) },
				},
			},
			scales: {
				x: {
					grid: { display: false },
					border: { color: '#2b4354' },
					ticks: { color: '#8faabb', maxTicksLimit: 6, autoSkipPadding: 12, maxRotation: 0, font: { size: 12 } },
				},
				y: {
					reverse: isRank,
					min: isRank ? 1 : undefined,
					grid: { color: '#263b4d80', drawTicks: false },
					border: { display: false },
					ticks: { color: '#8faabb', maxTicksLimit: 6, padding: 8, precision: 0, font: { size: 12 }, callback: (value) => number(value) },
				},
			},
		};
	}

	function updateCharts() {
		if (!rankChartRef.value || !pointChartRef.value) {
			rankChart?.destroy();
			pointsChart?.destroy();
			rankChart = null;
			pointsChart = null;
			return;
		}

		const labels = props.data.points.map((item) => dayjs(item.date).tz().format('DD MMM'));
		const rankData = {
			labels,
			datasets: Object.keys(typeChartLabels.value).map((category) => ({
				label: typeChartLabels.value[category],
				borderColor: typeChartColors[category],
				backgroundColor: typeChartColors[category],
				data: props.data.points.map((item) => item.rank[category]),
			})),
		};
		const pointData = {
			labels,
			datasets: [{
				label: typeChartLabels.value[typeChart.value],
				borderColor: typeChartColors[typeChart.value],
				backgroundColor: typeChartColors[typeChart.value],
				data: props.data.points.map((item) => item.point[typeChart.value]),
			}],
		};

		if (rankChart) {
			rankChart.data = rankData;
			rankChart.update('none');
		} else {
			rankChart = new Chart(rankChartRef.value, { type: 'line', data: rankData, options: chartOptions(true) });
		}
		if (pointsChart) {
			pointsChart.data = pointData;
			pointsChart.update('none');
		} else {
			pointsChart = new Chart(pointChartRef.value, { type: 'line', data: pointData, options: chartOptions(false) });
		}
	}

	onMounted(() => {
		Chart.register(CategoryScale, LinearScale, LineController, PointElement, LineElement, Legend, Tooltip);
		updateCharts();
	});
	watch([typeChart, () => props.data, typeChartLabels], updateCharts, { deep: true, flush: 'post' });
	onBeforeUnmount(() => {
		rankChart?.destroy();
		pointsChart?.destroy();
	});
</script>