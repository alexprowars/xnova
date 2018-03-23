<template>
	<table class="table">
		<tr>
			<td class="c" colspan="2">
				Обнаружена следующая активность на планете:
			</td>
		</tr>
			<tr v-if="page.items.length === 0">
				<th colspan="2">На этой планете нет движения флотов.</th>
			</tr>
			<tr v-for="(item, index) in page.items">
				<th>
					<div class="z">{{ Format.time(times[index], ':', true) }}</div>
					<font :color="item['direction'] === 1 ? 'lime' : 'green'">{{ date("H:i:s", item['time']) }}</font>
				</th>
				<th>
					<font :color="item['color']">
						Игрок (<span v-html="item['fleet']"></span>) с {{ item['type_1'] }} {{ item['planet_name'] }}
						<font color="white">[<span v-html="item['planet_position']"></span>]</font>
						{{ item['direction'] === 1 ? 'летит' : 'возвращается' }} на {{ item['type_2'] }} {{ item['target_name'] }}
						<font color="white">[<span v-html="item['target_position']"></span>]</font>.
						Задание: <font color="white">{{ item['mission'] }}</font>
					</font>
				</th>
			</tr>
	</table>
</template>

<script>
	export default {
		name: "phalanx",
		computed: {
			page () {
				return this.$store.state.page;
			},
		},
		data () {
			return {
				timer: null,
				times: []
			}
		},
		methods: {
			updateTimes ()
			{
				this.times = [];

				this.page.items.forEach((item) => {
					this.times.push(item['time'] - this.$root.serverTime())
				});

				this.startTimer();
			},
			startTimer () {
				this.timer = setTimeout(() => {
					this.updateTimes();
				}, 1000);
			},
			stopTimer () {
				clearTimeout(this.timer);
			}
		},
		mounted () {
			this.updateTimes();
		},
		destroyed () {
			this.stopTimer();
		}
	}
</script>