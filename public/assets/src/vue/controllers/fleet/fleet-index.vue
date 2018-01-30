<template>
	<div>
		<div class="table-responsive">
			<table class='table'>
				<tr>
					<td colspan='9' class='c'>
						<table border="0" width="100%">
							<tr>
								<td align="left">
									Флоты {{ page['curFleets'] }} из {{ page['maxFleets'] }}
								</td>
								<td align="right">
									{{ page['curExpeditions'] }}/{{ page['maxExpeditions'] }} Экспедиции
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<th width='20'>№</th>
					<th>Миссия</th>
					<th>Кол</th>
					<th>Дислокация</th>
					<th width='80'>Прибытие</th>
					<th>Цель</th>
					<th width='80'>Возврат</th>
					<th>Через</th>
					<th width='110'>Приказы</th>
				</tr>

				<tr v-for="(item, index) in page.fleets">
					<th>{{ index + 1 }}</th>
					<th>
						<a>{{ $root.getLang('FLEET_MISSION', item.mission) }}</a>
						<div v-if="item.start.time + 1 === item.target.time">
							<a title="Возврат домой">(R)</a>
						</div>
						<div v-else>
							<a title="Полёт к цели">(A)</a>
						</div>
					</th>
					<th>
						<a class="tooltip">
							<div class="tooltip-content">
								<div v-for="(fleetData, fleetId) in item.units">
									{{ $root.getLang('TECH', fleetId) }}: {{ fleetData['cnt'] }}
								</div>
							</div>
							{{ Format.number(item.amount) }}
						</a>
					</th>
					<th>
						<a :href="$root.getUrl('galaxy/'+item['start']['galaxy']+'/'+item['start']['system']+'/')">
							[{{ item['start']['galaxy'] }}:{{ item['start']['system'] }}:{{ item['start']['planet'] }}]
						</a>
					</th>
					<th>
						{{ date("d.m.y", item['start']['time']) }}<br>
						{{ date("H:i:s", item['start']['time']) }}
					</th>
					<th>
						<a :href="$root.getUrl('galaxy/'+item['target']['galaxy']+'/'+item['target']['system']+'/')">
							[{{ item['target']['galaxy'] }}:{{ item['target']['system'] }}:{{ item['target']['planet'] }}]
						</a>
					</th>
					<th>
						{{ date("d.m.y", item['target']['time']) }}<br>
						{{ date("H:i:s", item['target']['time']) }}
					</th>
					<th>
						<font color="lime">
							{{ (item['target']['time'] > $root.serverTime() ? Format.time(floor(item['target']['time'] + 1 - $root.serverTime()), ':', true) : 'обработка...') }}
						</font>
					</th>
					<th>
						<form v-if="item['stage'] === 0 && item['mission'] !== 20 && item.target.id !== 1" :action="$root.getUrl('fleet/back/')" method="post">
							<input name="fleetid" :value="item.id" type="hidden">
							<input value=" Возврат домой " type="submit" name="send">
						</form>

						<form v-if="item['stage'] === 0 && item['mission'] === 1 && item.target.id !== 1" :action="$root.getUrl('fleet/verband/')" method="post">
							<input name="fleetid" :value="item.id" type="hidden">
							<input value=" Объединить " type="submit">
						</form>

						<form v-if="item['stage'] === 3 && item['mission'] !== 15" :action="$root.getUrl('fleet/back/')" method="post">
							<input name="fleetid" :value="item.id" type="hidden">
							<input value=" Отозвать " type="submit" name="send">
						</form>
					</th>
				</tr>

				<tr v-if="page.fleets.length === 0">
					<th>-</th>
					<th>-</th>
					<th>-</th>
					<th>-</th>
					<th>-</th>
					<th>-</th>
					<th>-</th>
					<th>-</th>
					<th>-</th>
				</tr>

				<tr v-if="page['curFleets'] >= page['maxFleets']">
					<th colspan="9" class="negative">Все слоты заняты!</th>
				</tr>
			</table>
		</div>

		<br>
		<form :action="$root.getUrl('fleet/one/')" method="post">
			<div class="table fleet_ships container">
				<div class="row">
					<div class="col-12 c">
						Выбрать корабли<span v-if="page['selected']['mission'] > 0"> для миссии "{{ $root.getLang('FLEET_MISSION', page['selected']['mission']) }}"</span><span v-if="page['selected']['galaxy'] > 0"> на координаты [{{ page['selected']['galaxy'] }}:{{ page['selected']['system'] }}:{{ page['selected']['planet'] }}]</span>:
					</div>
				</div>
				<div class="row">
					<div class="th col-sm-7 col-6">Тип корабля</div>
					<div class="th col-sm-2 col-2">Кол-во</div>
					<div class="th col-sm-3 col-4">&nbsp;</div>
				</div>

				<div v-for="(ship, index) in page.ships" class="row">
					<div class="th col-sm-7 col-6 middle">
						<a :title="$root.getLang('TECH', ship.id)">{{ $root.getLang('TECH', ship.id) }}</a>
					</div>
					<div class="th col-sm-2 col-2 middle">
						<a v-on:click.prevent="maxShips(index)">{{ Format.number(ship['count']) }}</a>
					</div>
					<div v-if="ship.id === 212" class="th col-sm-3 col-4"></div>
					<div v-else="" class="th col-sm-3 col-4">
						<a v-on:click.prevent="diffShips(index, -1)" title="Уменьшить на 1" style="color:#FFD0D0">- </a>
						<input type="number" min="0" :max="ship['count']" :name="'ship['+ship['id']+']'" v-model="fleets[index].count" style="width:60%" :title="$root.getLang('TECH', ship.id)+': '+ship['count']" placeholder="0" v-on:change.prevent="calculateShips" v-on:keyup="calculateShips">
						<a v-on:click.prevent="diffShips(index, 1)" title="Увеличить на 1" style="color:#D0FFD0"> +</a>
					</div>
				</div>

				<div v-if="page.ships.length === 0" class="row">
					<div class="th col-12">Нет кораблей!</div>
				</div>
				<div v-else>
					<div class="row">
						<div class="col-12 col-sm-7 th"></div>
						<div class="col-12 col-sm-5 th">
							<a class="button" v-on:click.prevent="allShips">Все корабли</a>
							<a v-if="count" class="button" v-on:click.prevent="clearShips">Очистить</a>
						</div>
					</div>
					<div v-if="count" class="row">
						<div class="th col-4 col-sm-7">&nbsp;</div>
						<div class="th col-4 col-sm-2">Вместимость</div>
						<div class="th col-4 col-sm-3">{{ allCapacity ? Format.number(allCapacity) : '-' }}</div>
					</div>
					<div v-if="count" class="row">
						<div class="th col-4 col-sm-7">&nbsp;</div>
						<div class="th col-4 col-sm-2">Скорость</div>
						<div class="th col-4 col-sm-3">{{ allSpeed ? Format.number(allSpeed) : '-'}}</div>
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
		</form>
	</div>
</template>

<script>
	export default {
		name: "fleet-index",
		computed: {
			page () {
				return this.$store.state.page;
			},
			count () {
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
		methods: {
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
		},
		created ()
		{
			this.page.ships.forEach(() => {
				this.fleets.push({count: ''});
			});
		}
	}
</script>