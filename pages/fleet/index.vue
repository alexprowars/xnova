<template>
	<div class="page-fleet">
		<div class="page-fleet-fly block">
			<div class="title">
				<div class="row">
					<div class="col text-left">
						Флоты <span :class="[page['curFleets'] < page['maxFleets'] ? 'positive' : 'negative']">{{ page['curFleets'] }}</span> из <span class="negative">{{ page['maxFleets'] }}</span>
					</div>
					<div v-if="page['maxExpeditions'] > 0" class="col text-right">
						Экспедиции {{ page['curExpeditions'] }}/{{ page['maxExpeditions'] }}
					</div>
				</div>
			</div>

			<div class="content">
				<div class="table">
					<div class="row">
						<div class="col-3 col-sm-1 th">№</div>
						<div class="col-6 col-sm-2 th">Миссия</div>
						<div class="col-3 col-sm-1 th">Кол-во</div>
						<div class="col-4 col-sm-3 th d-none d-sm-block">Цель</div>
						<div class="col-2 col-sm-3 th d-none d-sm-block">Возврат</div>
						<div class="col-2 th d-none d-sm-block">Приказы</div>
					</div>

					<fly-row class="row page-fleet-fly-item" v-for="(item, index) in page.fleets" :key="index" :i="index" :item="item"></fly-row>

					<div class="row" v-if="page.fleets.length === 0">
						<div class="th col text-center">-</div>
					</div>

					<div class="row" v-if="page['curFleets'] >= page['maxFleets']">
						<div class="th col negative text-center">Все слоты заняты!</div>
					</div>
				</div>
			</div>
		</div>

		<br>
		<div class="block page-fleet-select">
			<div class="title">
				<div class="row">
					<div class="col-12 text-center">
						Выбрать корабли<span v-if="page['selected']['mission'] > 0"> для миссии "{{ $t('FLEET_MISSION.'+page['selected']['mission']) }}"</span><span v-if="page['selected']['galaxy'] > 0"> на координаты [{{ page['selected']['galaxy'] }}:{{ page['selected']['system'] }}:{{ page['selected']['planet'] }}]</span>:
					</div>
				</div>
			</div>
			<div class="content">
				<router-form action="/fleet/one/">
					<div class="table fleet_ships container">
						<div class="row">
							<div class="th col-sm-7 col-6">Тип корабля</div>
							<div class="th col-sm-2 col-2">Кол-во</div>
							<div class="th col-sm-3 col-4">&nbsp;</div>
						</div>

						<div v-for="(ship, index) in page.ships" class="row">
							<div class="th col-sm-7 col-6 middle">
								<a :title="$t('TECH.'+ship.id)">{{ $t('TECH.'+ship.id) }}</a>
							</div>
							<div class="th col-sm-2 col-2 middle">
								<a @click.prevent="maxShips(index)">{{ ship['count']|number }}</a>
							</div>
							<div v-if="ship.id === 212" class="th col-sm-3 col-4"></div>
							<div v-else="" class="th col-sm-3 col-4">
								<a @click.prevent="diffShips(index, -1)" title="Уменьшить на 1" style="color:#FFD0D0">- </a>
								<input type="number" min="0" :max="ship['count']" :name="'ship['+ship['id']+']'" v-model="fleets[index].count" style="width:60%" :title="$t('TECH.'+ship.id)+': '+ship['count']" placeholder="0" @change.prevent="calculateShips" @keyup="calculateShips">
								<a @click.prevent="diffShips(index, 1)" title="Увеличить на 1" style="color:#D0FFD0"> +</a>
							</div>
						</div>

						<div v-if="page.ships.length === 0" class="row">
							<div class="th col-12">Нет кораблей!</div>
						</div>
						<div v-else>
							<div class="row">
								<div class="col-12 col-sm-7 th"></div>
								<div class="col-12 col-sm-5 th">
									<a class="button" @click.prevent="allShips">Все корабли</a>
									<a v-if="count" class="button" @click.prevent="clearShips">Очистить</a>
								</div>
							</div>
							<div v-if="count" class="row">
								<div class="th col-4 col-sm-7">&nbsp;</div>
								<div class="th col-4 col-sm-2">Вместимость</div>
								<div class="th col-4 col-sm-3">{{ allCapacity ? $options.filters.number(allCapacity) : '-' }}</div>
							</div>
							<div v-if="count" class="row">
								<div class="th col-4 col-sm-7">&nbsp;</div>
								<div class="th col-4 col-sm-2">Скорость</div>
								<div class="th col-4 col-sm-3">{{ allSpeed ? $options.filters.number(allSpeed) : '-'}}</div>
							</div>
							<div v-if="count && page['curFleets'] < page['maxFleets']" class="row">
								<div class="th col-12"><input type="submit" value=" Далее "></div>
							</div>
						</div>
					</div>
					<input type="hidden" name="galaxy" :value="page['selected']['galaxy']">
					<input type="hidden" name="system" :value="page['selected']['system']">
					<input type="hidden" name="planet" :value="page['selected']['planet']">
					<input type="hidden" name="planet_type" :value="page['selected']['planet_type']">
					<input type="hidden" name="mission" :value="page['selected']['mission']">
				</router-form>
			</div>
		</div>
	</div>
</template>

<script>
	import FlyRow from '~/components/page/fleet/fly-row.vue'

	export default {
		name: "fleet-index",
		components: {
			FlyRow
		},
		asyncData ({ store, route }) {
			return store.dispatch('loadPage', route.fullPath)
		},
		watchQuery: true,
		middleware: ['auth'],
		computed: {
			count ()
			{
				if (!this.fleets)
					return 0

				return this.fleets.reduce((total, item) => {
					return total + (item.count === '' ? 0 : parseInt(item.count));
				}, 0);
			}
		},
		data () {
			return {
				fleets: [],
				allCapacity: 0,
				allSpeed: 0
			}
		},
		watch: {
			'page.ships' () {
				this.init();
			}
		},
		created () {
			this.init();
		},
		methods: {
			init ()
			{
				if (!this.page || !this.page.ships)
					return;

				this.fleets = [];
				this.page.ships.forEach(() => {
					this.fleets.push({count: ''});
				});
				this.calculateShips();
			},
			maxShips (index)
			{
				let fleet = this.page['ships'][index];

				if (this.fleets[index].count === fleet.count)
					this.fleets[index].count = '';
				else
					this.fleets[index].count = fleet.count;

				this.calculateShips();
			},
			clearShips ()
			{
				this.fleets.forEach((item) => item.count = '');
				this.calculateShips();
			},
			allShips ()
			{
				this.fleets.forEach((item, index) => item.count = this.page['ships'][index].count);
				this.calculateShips();
			},
			diffShips (index, val)
			{
				if (isNaN(parseInt(this.fleets[index].count)))
					this.fleets[index].count = 0;

				this.fleets[index].count += val;

				if (this.fleets[index].count <= 0)
					this.fleets[index].count = '';

				if (this.fleets[index].count > this.page['ships'][index].count)
					this.fleets[index].count = this.page['ships'][index].count;

				this.calculateShips();
			},
			calculateShips ()
			{
				let maxSpeed = 1000000000;
				let capacity = 0;
				let speed = maxSpeed;

				this.fleets.forEach((item, index) =>
				{
					let cnt = parseInt(item.count);

					if (isNaN(cnt))
						return;

					capacity += cnt * this.page['ships'][index]['capacity'];

					if (cnt > 0 && this.page['ships'][index]['speed'] > 0 && this.page['ships'][index]['speed'] < speed)
						speed = this.page['ships'][index]['speed'];
				})

				if ((speed <= 0) || (speed >= maxSpeed))
					speed = 0;

				this.allSpeed = speed;
				this.allCapacity = capacity;
			}
		}
	}
</script>