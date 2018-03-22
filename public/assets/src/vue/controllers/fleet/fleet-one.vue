<template>
	<form :action="$root.getUrl('fleet/two/')" method="post">
		<input v-for="ship in page.ships" type="hidden" :name="'ship['+ship.id+']'" :value="ship['count']">
		<div class="table">
			<div class="row">
				<div class="c col-12">Отправление флота</div>
			</div>
			<div class="row">
				<div class="th col-6">Цель</div>
				<div class="th col-6 fleet-coordinates-input">
					<input type="number" name="galaxy" min="1" :max="page['galaxy_max']" v-model="page['target']['galaxy']" title="">
					<input type="number" name="system" min="1" :max="page['system_max']" v-model="page['target']['system']" title="">
					<input type="number" name="planet" min="1" :max="page['planet_max']" v-model="page['target']['planet']" title="">
					<select name="planet_type" v-model="page['target']['planet_type']" title="">
						<option v-for="(item, index) in $root.getLang('PLANET_TYPE')" :value="index">{{ item }}</option>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="th col-6">Скорость</div>
				<div class="th col-6">
					<select name="speed" v-model="speed" @change="info" title="">
						<option v-for="i in 10" :value="11 - i">{{ (11 - i) * 10 }}</option>
					</select> %
				</div>
			</div>
			<div class="row">
				<div class="th col-6">Расстояние</div>
				<div class="th col-6">{{ Format.number(distance) }}</div>
			</div>
			<div class="row">
				<div class="th col-6">Продолжительность полёта (к цели)</div>
				<div class="th col-6">{{ Format.time(duration, ':', true) }}</div>
			</div>
			<div class="row">
				<div class="th col-6">Время прибытия (к цели)</div>
				<div class="th col-6">{{ date('d.m.Y H:i:s', target_time) }}</div>
			</div>
			<div class="row">
				<div class="th col-6">Максимальная скорость</div>
				<div class="th col-6">{{ Format.number(maxspeed) }}</div>
			</div>
			<div class="row">
				<div class="th col-6">Потребление топлива</div>
				<div class="th col-6"><span :class="[storage > consumption ? 'positive' : 'negative']">{{ Format.number(consumption) }}</span></div>
			</div>
			<div class="row">
				<div class="th col-6">Грузоподъёмность</div>
				<div class="th col-6"><span :class="[storage > consumption ? 'positive' : 'negative']">{{ Format.number(storage) }}</span></div>
			</div>
			<div class="row">
				<div class="c col-12">Ссылки <a :href="$root.getUrl('fleet/shortcut/')">(Просмотр / Редактирование)</a></div>
			</div>

			<div v-if="page['shortcuts'].length > 0" class="row">
				<div v-for="link in page['shortcuts']" class="th col-6">
					<a @click.prevent="setTarget(link[1], link[2], link[3], link[4])">
						{{ link[0] }} {{ link[1] }}:{{ link[2] }}:{{ link[3] }}
						<span v-if="link[4] === 1">(P)</span>
						<span v-if="link[4] === 2">(D)</span>
						<span v-if="link[4] === 3">(L)</span>
					</a>
				</div>
			</div>

			<div v-if="page['planets'].length > 0" class="row">
				<div class="c col-12">Планеты</div>
			</div>
			<div v-if="page['planets'].length > 0" class="row">
				<div v-for="planet in page['planets']" class="th col-6">
					<a @click.prevent="setTarget(planet['galaxy'], planet['system'], planet['planet'], planet['planet_type'])">
						{{ planet['name'] }} {{ planet['galaxy'] }}:{{ planet['system'] }}:{{ planet['planet'] }}
					</a>
				</div>
			</div>

			<div v-if="page['moons'].length > 0" class="row">
				<div class="c col-12">
					Межгалактические врата{% if parse['moon_timer'] != '' %} - <span id="bxxGate1"></span>{{ parse['moon_timer'] }}{% endif %}
				</div>
			</div>
			<div v-if="page['moons'].length > 0" class="row">
				<div v-for="moon in page['moons']" class="th col-6">
					<input type="radio" name="moon" value="{{ moon['id'] }}" :id="'moon'+moon['id']">
					<label :for="'moon'+moon['id']">{{ moon['name'] }} [{{ moon['galaxy'] }}:{{ moon['system'] }}:{{ moon['planet'] }}] {{ Format.time(moon['timer'], ':', true) }}</label>
				</div>
			</div>

			<div v-if="page['alliances'].length > 0" class="row">
				<div class="c col-12">Боевые союзы</div>
			</div>
			<div v-for="(row, index) in page['alliances']" class="row">
				<div class="th col-12">
					<a @click.prevent="allianceSet(index)">({{ row['name'] }})</a>
				</div>
			</div>

			<div class="row">
				<div class="th col-12"><input type="submit" value="Далее"></div>
			</div>
		</div>
		<input type="hidden" name="alliance" v-model="alliance">
		<input type="hidden" name="fleet" :value="page['fleet']">
		<input type="hidden" name="mission" :value="page['mission']">
	</form>
</template>

<script>
	export default {
		name: "fleet-one",
		computed: {
			page () {
				return this.$store.state.page;
			},
			position () {
				return this.$store.state.user.position;
			},
		},
		data () {
			return {
				speed: 10,
				distance: 0,
				duration: 0,
				storage: 0,
				maxspeed: 0,
				consumption: 0,

				target_time: 0,
				target_timeout: null,

				alliance: 0
			}
		},
		watch: {
			target_time () {
				this.startTimer();
			},
			'page.target': {
				handler () {
					this.info();
				},
				deep: true,
			}
		},
		methods: {
			info ()
			{
				let fleet = require('./../../js/fleet.js');

				this.distance = fleet.distance(this.position, this.page['target']);
				this.maxspeed = fleet.speed(this.page['ships']);

				this.duration = fleet.duration({
					factor: this.speed,
					distance: this.distance,
					max_speed: this.maxspeed,
					universe_speed: this.$store.state['speed']['fleet']
				});

				this.consumption = fleet.consumption({
					ships: this.page['ships'],
					duration: this.duration,
					distance: this.distance,
					universe_speed: this.$store.state['speed']['fleet']
				});

				this.storage = fleet.storage(this.page['ships']) - this.consumption;

				this.clearTimer();
				this.target_time = this.$root.serverTime() + this.duration;
			},
			startTimer () {
				this.target_timeout = setTimeout(() => this.target_time = this.$root.serverTime() + this.duration, 1000);
			},
			clearTimer () {
				clearTimeout(this.target_timeout);
			},
			setTarget (galaxy, system, planet, type)
			{
				this.page['target']['galaxy'] = galaxy;
				this.page['target']['system'] = system;
				this.page['target']['planet'] = planet;

				if (typeof type === 'undefined')
					type = 1;

				this.page['target']['planet_type'] = type;
			},
			allianceSet (index)
			{
				let al = this.page['alliances'][index];

				this.alliance = al['id'];
				this.setTarget(al['galaxy'], al['system'], al['planet'], al['planet_type']);
			}
		},
		mounted () {
			this.info();
		},
		destroyed: function () {
			this.clearTimer();
		}
	}
</script>