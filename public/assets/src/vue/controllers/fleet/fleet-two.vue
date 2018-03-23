<template>
	<form :action="$root.getUrl('fleet/three/')" method="post">
		<input v-for="ship in page.ships" type="hidden" :name="'ship['+ship.id+']'" :value="ship['count']">

		<input type="hidden" name="fleet" :value="page['fleet']">
		<input type="hidden" name="speed" :value="page['speed']">
		<input type="hidden" name="alliance" :value="page['alliance']">
		<input type="hidden" name="galaxy" :value="page['target']['galaxy']">
		<input type="hidden" name="system" :value="page['target']['system']">
		<input type="hidden" name="planet" :value="page['target']['planet']">
		<input type="hidden" name="planet_type" :value="page['target']['planet_type']">

		<div class="table">
			<div class="row">
				<div class="c col-12">{{ page['target']['galaxy'] }}:{{ page['target']['system'] }}:{{ page['target']['planet'] }} - {{ $root.getLang('PLANET_TYPE', page['target']['planet_type']) }}</div>
			</div>
			<div class="row">
				<div class="th col-6">
					<table class="table">
						<tr>
							<td class="c" colspan="2">Миссия</td>
						</tr>
						<tr v-for="mission in page['missions']">
							<th style="text-align: left !important">
								<input :id="'m_'+mission" type="radio" name="mission" v-model="page['mission']" :value="mission">
								<label :for="'m_'+mission">{{ $root.getLang('FLEET_MISSION', mission) }}</label>

								<center v-if="mission === 15">
									<font color="red">Внимание во время экспедиции возможна потеря флота!</font>
								</center>
							</th>
						</tr>
						<tr v-if="page['missions'].length === 0">
							<th><font color="red">Миссия не возможна!</font></th>
						</tr>
						<tr>
							<th>Время прилёта: {{ date('d.m.Y H:i:s', target_time) }}</th>
						</tr>
					</table>
				</div>
				<div class="th col-6">
					<table class="table">
						<tr>
							<td colspan="3" class="c">Сырьё</td>
						</tr>
						<tr>
							<th>Металл</th>
							<th><a v-on:click.prevent="maxRes('metal')">макс.</a></th>
							<th><input name="resource[metal]" v-model="resource.metal" alt="Металл" size="10" type="text" title=""></th>
						</tr>
						<tr>
							<th>Кристалл</th>
							<th><a v-on:click.prevent="maxRes('crystal')">макс.</a></th>
							<th><input name="resource[crystal]" v-model="resource.crystal" alt="Кристалл" size="10" type="text" title=""></th>
						</tr>
						<tr>
							<th>Дейтерий</th>
							<th><a v-on:click.prevent="maxRes('deuterium')">макс.</a></th>
							<th><input name="resource[deuterium]" v-model="resource.deuterium" alt="Дейтерий" size="10" type="text" title=""></th>
						</tr>
						<tr>
							<th>Остаток</th>
							<th colspan="2">
								<span :class="[capacity >= 0 ? 'positive' : 'negative']">{{ Format.number(capacity) }}</span>
							</th>
						</tr>
						<tr>
							<th colspan="3"><a v-on:click.prevent="maxResAll">Всё сырьё</a> | <a v-on:click.prevent="clearResAll">Обнулить</a></th>
						</tr>
						<tr>
							<th colspan="3">&nbsp;</th>
						</tr>

						<tr v-if="page['mission'] === 15 && page['missions'].indexOf(15) >= 0" class="mission m_15">
							<td class="c" colspan="3">Время экспедиции</td>
						</tr>
						<tr v-if="page['mission'] === 15 && page['missions'].indexOf(15) >= 0" class="mission m_15">
							<th colspan="3">
								<select name="expeditiontime" title="">
									<option v-for="i in page['expedition_hours']" :value="i">{{ i }} ч.</option>
								</select>
							</th>
						</tr>

						<tr v-if="page['mission'] === 5 && page['missions'].indexOf(5) >= 0" class="mission m_5">
							<td class="c" colspan="3">Оставаться часов на орбите</td>
						</tr>
						<tr v-if="page['mission'] === 5 && page['missions'].indexOf(5) >= 0" class="mission m_5">
							<th colspan="3">
								<select name="holdingtime" v-model="hold_hours" title="">
									<option value="0">0</option>
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="4">4</option>
									<option value="8">8</option>
									<option value="16">16</option>
									<option value="32">32</option>
								</select>
								<div v-if="hold > 0">
									<br>Потребуется <span class="positive">{{ Format.number(hold) }}</span> дейтерия
								</div>
							</th>
						</tr>
					</table>
				</div>
			</div>
			<div v-if="page['missions'].length > 0" class="row">
				<div class="th col-12">
					<input value="Далее" type="submit">
				</div>
			</div>
		</div>
	</form>
</template>

<script>
	export default {
		name: "fleet-two",
		computed: {
			page () {
				return this.$store.state.page;
			},
			resources () {
				return this.$store.state.resources;
			},
			position () {
				return this.$store.state.user.position;
			},
			hold ()
			{
				let hold = 0;

				if (this.page['mission'] === 5)
					hold = this.page['hold'] * this.hold_hours;

				return hold;
			},
			capacity () {
				return this.storage - this.resource.metal - this.resource.crystal - this.resource.deuterium - this.hold;
			}
		},
		data () {
			return {
				resource: {
					metal: 0,
					crystal: 0,
					deuterium: 0,
				},
				storage: 0,
				consumption: 0,
				duration: 0,
				hold_hours: 1,

				target_time: 0,
				target_timeout: null,
			}
		},
		watch: {
			target_time () {
				this.startTimer();
			}
		},
		methods: {
			maxRes (type)
			{
				let current = this.resource.metal + this.resource.crystal + this.resource.deuterium;
				current -= this.resource[type];

				let free = this.storage - current;

				if (type === 'deuterium')
					this.resource[type] = Math.max(Math.min(Math.floor(this.resources[type]['current'] - this.consumption), free), 0);
				else
					this.resource[type] = Math.max(Math.min(Math.floor(this.resources[type]['current']), free), 0);
			},
			maxResAll ()
			{
				let free = this.storage - Math.floor(this.resources['metal']['current']) - Math.floor(this.resources['crystal']['current']) - Math.floor(this.resources['deuterium']['current'] - this.consumption);

				if (free < 0)
				{
					this.resource.metal = Math.max(Math.min(Math.floor(this.resources['metal']['current']), this.storage), 0);
					this.resource.crystal = Math.max(Math.min(Math.floor(this.resources['crystal']['current']), this.storage - this.resource.metal), 0);
					this.resource.deuterium = Math.max(Math.min(Math.floor(this.resources['deuterium']['current'] - this.consumption), this.storage - this.resource.metal - this.resource.crystal), 0);
				}
				else
				{
					this.resource.metal = Math.max(Math.floor(this.resources['metal']['current']), 0);
					this.resource.crystal = Math.max(Math.floor(this.resources['crystal']['current']), 0);
					this.resource.deuterium = Math.max(Math.floor(this.resources['deuterium']['current'] - this.consumption), 0);
				}
			},
			clearResAll ()
			{
				this.resource.metal = this.resource.crystal = this.resource.deuterium = 0;
			},
			startTimer () {
				this.target_timeout = setTimeout(() => this.target_time = this.$root.serverTime() + this.duration, 1000);
			},
			clearTimer () {
				clearTimeout(this.target_timeout);
			},
		},
		created ()
		{
			let fleet = require('./../../js/fleet.js');

			let distance = fleet.distance(this.position, this.page['target']);
			let maxspeed = fleet.speed(this.page['ships']);

			let duration = fleet.duration({
				factor: this.page['speed'],
				distance: distance,
				max_speed: maxspeed,
				universe_speed: this.$store.state['speed']['fleet']
			});

			this.consumption = fleet.consumption({
				ships: this.page['ships'],
				duration: duration,
				distance: distance,
				universe_speed: this.$store.state['speed']['fleet']
			});

			this.storage = fleet.storage(this.page['ships']) - this.consumption;
			this.duration = duration;
		},
		mounted () {
			this.target_time = this.$root.serverTime() + this.duration
		}
	}
</script>