<template>
	<router-form v-if="page" action="/fleet/two/">
		<input v-for="ship in page.ships" type="hidden" :name="'ship['+ship.id+']'" :value="ship['count']">
		<div class="table">
			<div class="row">
				<div class="c col-12">Отправление флота</div>
			</div>
			<div class="row">
				<div class="th col-6">Цель</div>
				<div class="th col-6 fleet-coordinates-input">
					<input type="number" name="galaxy" min="1" :max="page['galaxy_max']" v-model="page['target']['galaxy']">
					<input type="number" name="system" min="1" :max="page['system_max']" v-model="page['target']['system']">
					<input type="number" name="planet" min="1" :max="page['planet_max']" v-model="page['target']['planet']">
					<select name="planet_type" v-model="page['target']['planet_type']">
						<option v-for="(item, index) in $t('PLANET_TYPE')" :value="index">{{ item }}</option>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="th col-6">Скорость</div>
				<div class="th col-6">
					<select name="speed" v-model="speed" @change="info">
						<option v-for="i in 10" :value="11 - i">{{ (11 - i) * 10 }}</option>
					</select> %
				</div>
			</div>
			<div class="row">
				<div class="th col-6">Расстояние</div>
				<div class="th col-6">{{ distance|number }}</div>
			</div>
			<div class="row">
				<div class="th col-6">Продолжительность полёта (к цели)</div>
				<div class="th col-6">{{ duration|time(':', true) }}</div>
			</div>
			<div class="row">
				<div class="th col-6">Время прибытия (к цели)</div>
				<div class="th col-6">{{ target_time|date('d.m.Y H:i:s') }}</div>
			</div>
			<div class="row">
				<div class="th col-6">Максимальная скорость</div>
				<div class="th col-6">{{ maxspeed|number }}</div>
			</div>
			<div class="row">
				<div class="th col-6">Потребление топлива</div>
				<div class="th col-6"><span :class="[storage > consumption ? 'positive' : 'negative']">{{ consumption|number }}</span></div>
			</div>
			<div class="row">
				<div class="th col-6">Грузоподъёмность</div>
				<div class="th col-6"><span :class="[storage > consumption ? 'positive' : 'negative']">{{ storage|number }}</span></div>
			</div>
			<div class="row">
				<div class="c col-12">Ссылки <nuxt-link to="/fleet/shortcut/">(Просмотр / Редактирование)</nuxt-link></div>
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
				<div v-for="(planet, i) in page['planets']" class="th" :class="['col-'+(page['planets'].length % 2 > 0 && i === page['planets'].length - 1 ? 12 : 6)]">
					<a @click.prevent="setTarget(planet['galaxy'], planet['system'], planet['planet'], planet['planet_type'])">
						{{ planet['name'] }} {{ planet['galaxy'] }}:{{ planet['system'] }}:{{ planet['planet'] }}
					</a>
				</div>
			</div>

			<div v-if="page['moons'].length > 0" class="row">
				<div class="c col-12">
					Межгалактические врата
					<span v-if="page['gate_time'] > 0" class="small">(заряжено через {{ page['gate_time']|time(':', true) }})</span>
				</div>
			</div>
			<div v-if="page['moons'].length > 0" class="row">
				<div v-for="(moon, i) in page['moons']" class="th" :class="['col-'+(page['moons'].length % 2 > 0 && i === page['moons'].length - 1 ? 12 : 6)]">
					<input type="radio" name="moon" :value="moon['id']" :id="'moon'+moon['id']">
					<label :for="'moon'+moon['id']">{{ moon['name'] }} [{{ moon['galaxy'] }}:{{ moon['system'] }}:{{ moon['planet'] }}] <span v-if="moon['timer'] > 0">{{ moon['timer']|time(':', true) }}</span></label>
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
	</router-form>
</template>

<script>
	import { getDistance, getSpeed, getDuration, getConsumption, getStorage } from '~/utils/fleet'

	export default {
		name: 'fleet-one',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		middleware: 'auth',
		computed: {
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
				this.startTimer()
			},
			'page.target': {
				handler () {
					this.info()
				},
				deep: true,
			}
		},
		methods: {
			info ()
			{
				if (!this.page)
					return

				this.distance = getDistance(this.position, this.page['target'])
				this.maxspeed = getSpeed(this.page['ships'])

				this.duration = getDuration({
					factor: this.speed,
					distance: this.distance,
					max_speed: this.maxspeed,
					universe_speed: this.$store.state['speed']['fleet']
				})

				this.consumption = getConsumption({
					ships: this.page['ships'],
					duration: this.duration,
					distance: this.distance,
					universe_speed: this.$store.state['speed']['fleet']
				})

				this.storage = getStorage(this.page['ships']) - this.consumption

				this.clearTimer()
				this.target_time = this.$store.getters.getServerTime() + this.duration
			},
			startTimer ()
			{
				this.target_timeout = setTimeout(() =>
				{
					this.target_time = this.$store.getters.getServerTime() + this.duration

					if (this.page['gate_time'] > 0)
						this.page['gate_time']--

					this.page['moons'].forEach((item) =>
					{
						if (item['timer'] > 0)
							item['timer']--
					})

				}, 1000)
			},
			clearTimer () {
				clearTimeout(this.target_timeout)
			},
			setTarget (galaxy, system, planet, type)
			{
				this.page['target']['galaxy'] = galaxy
				this.page['target']['system'] = system
				this.page['target']['planet'] = planet

				if (typeof type === 'undefined')
					type = 1

				this.page['target']['planet_type'] = type
			},
			allianceSet (index)
			{
				let al = this.page['alliances'][index]

				this.alliance = al['id']
				this.setTarget(al['galaxy'], al['system'], al['planet'], al['planet_type'])
			}
		},
		mounted () {
			this.info()
		},
		destroyed () {
			this.clearTimer()
		}
	}
</script>