<template>
	<div v-if="page" class="page-fleet">
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
					<div v-if="page.fleets.length > 0" class="row">
						<div class="col-3 col-sm-1 th">№</div>
						<div class="col-6 col-sm-2 th">Миссия</div>
						<div class="col-3 col-sm-1 th">Кол-во</div>
						<div class="col-4 col-sm-3 th d-none d-sm-block">Цель</div>
						<div class="col-2 col-sm-3 th d-none d-sm-block">Возврат</div>
						<div class="col-2 th d-none d-sm-block">Приказы</div>
					</div>

					<FlyRow class="row page-fleet-fly-item" v-for="(item, index) in page.fleets" :key="index" :i="index" :item="item"></FlyRow>

					<div class="row" v-if="page.fleets.length === 0">
						<div class="th col text-center">-</div>
					</div>

					<div class="row" v-if="page['curFleets'] >= page['maxFleets']">
						<div class="th col negative text-center">Все слоты заняты!</div>
					</div>
				</div>
			</div>
		</div>
		<div class="block page-fleet-select">
			<div class="title">
				<div class="row">
					<div class="col-12 text-center">
						Выбрать корабли<template v-if="page['selected']['mission'] > 0"> для миссии "{{ $t('FLEET_MISSION.'+page['selected']['mission']) }}"</template><template v-if="page['selected']['galaxy'] > 0"> на координаты [{{ page['selected']['galaxy'] }}:{{ page['selected']['system'] }}:{{ page['selected']['planet'] }}]</template>:
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

						<div v-for="ship in page.ships" class="row">
							<div class="th col-sm-7 col-6 middle">
								<a :title="$t('TECH.'+ship.id)">{{ $t('TECH.'+ship.id) }}</a>
							</div>
							<div class="th col-sm-2 col-2 middle">
								<a @click.prevent="maxShips(ship['id'])">{{ ship['count']|number }}</a>
							</div>
							<div v-if="ship.id === 212" class="th col-sm-3 col-4"></div>
							<div v-else="" class="th col-sm-3 col-4">
								<a @click.prevent="diffShips(ship['id'], -1)" title="Уменьшить на 1" style="color:#FFD0D0">- </a>
								<input type="number" min="0" :max="ship['count']" :name="'ship['+ship['id']+']'" v-model.number="fleets[ship['id']]" style="width:60%" :title="$t('TECH.'+ship.id)+': '+ship['count']" placeholder="0" @change.prevent="calculateShips" @keyup="calculateShips">
								<a @click.prevent="diffShips(ship['id'], 1)" title="Увеличить на 1" style="color:#D0FFD0"> +</a>
							</div>
						</div>

						<div v-if="page.ships.length === 0" class="row">
							<div class="th col-12">Нет кораблей!</div>
						</div>
						<div v-else>
							<div class="row">
								<div class="col-12 col-sm-7 th"></div>
								<div class="col-12 col-sm-5 th">
									<a class="button" @click.prevent="allShips">Выбрать все</a>
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
		name: 'fleet-index',
		components: {
			FlyRow
		},
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		computed: {
			count ()
			{
				return this.page['ships'].reduce((total, ship) =>
				{
					let cnt = this.fleets[ship.id] || 0;
					return (total + cnt);
				}, 0);
			}
		},
		data () {
			return {
				fleets: {},
				allCapacity: 0,
				allSpeed: 0
			}
		},
		watch: {
			'page.ships' () {
				this.init();
			},
			'fleets': {
				handler () {
					this.calculateShips();
				},
				deep: true,
			}
		},
		methods: {
			init ()
			{
				if (!this.page || !this.page.ships)
					return;

				this.fleets = {};
			},
			maxShips (shipId)
			{
				let ship = this.page['ships'].find((item) => {
					return item.id === shipId
				})

				if (typeof this.fleets[ship['id']] !== "undefined" && this.fleets[ship['id']] === ship['count'])
					this.fleets[ship['id']] = ''
				else
					this.$set(this.fleets, ship['id'], ship['count']);
			},
			clearShips ()
			{
				this.page.ships.forEach((ship) => {
					this.$set(this.fleets, ship['id'], '');
				})
			},
			allShips ()
			{
				this.page.ships.forEach((ship) =>
				{
					if (ship['id'] !== 212)
						this.$set(this.fleets, ship['id'], ship['count']);
				})
			},
			diffShips (shipId, val)
			{
				if (typeof this.fleets[shipId] === "undefined")
					this.$set(this.fleets, shipId, 0);

				if (!parseInt(this.fleets[shipId]))
					this.fleets[shipId] = 0;

				this.fleets[shipId] += val;

				if (this.fleets[shipId] <= 0)
					this.fleets[shipId] = '';

				let ship = this.page['ships'].find((item) => {
					return item.id === shipId
				})

				if (this.fleets[shipId] > ship.count)
					this.fleets[shipId] = ship.count;
			},
			calculateShips ()
			{
				let maxSpeed = 1000000000;
				let capacity = 0;
				let speed = maxSpeed;

				this.page['ships'].forEach((ship) =>
				{
					let cnt = this.fleets[ship.id] || 0;
					cnt = parseInt(cnt);

					if (isNaN(cnt))
						return;

					capacity += cnt * ship['capacity'];

					if (cnt > 0 && ship['speed'] > 0 && ship['speed'] < speed)
						speed = ship['speed'];
				})

				if ((speed <= 0) || (speed >= maxSpeed))
					speed = 0;

				this.allSpeed = speed;
				this.allCapacity = capacity;
			}
		}
	}
</script>