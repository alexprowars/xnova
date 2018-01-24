<template>
	<div class="row topnav">
		<div class="col-md-6 col-sm-6 col-12">
			<div class="row">
				<div class="col-4 text-center"><planet-panel-resource :type="'metal'" :resource="planet.metal"></planet-panel-resource></div>
				<div class="col-4 text-center"><planet-panel-resource :type="'crystal'" :resource="planet.crystal"></planet-panel-resource></div>
				<div class="col-4 text-center"><planet-panel-resource :type="'deuterium'" :resource="planet.deuterium"></planet-panel-resource></div>
			</div>
		</div>
		<div class="col-md-6 col-sm-6 col-12">
			<div class="row">
				<div class="col-4 text-center">
					<div class="resource-panel-item">
						<div onclick="showWindow('', '/info/4/')" title="Солнечная батарея" class="tooltip">
							<div class="tooltip-content">
								<div class="resource-panel-item-tooltip">
									<h1>Энергия</h1>
									<div class="line"></div>
									<table>
										<tr>
											<td>Доступно:</td>
											<td align="right">{{ Format.number(planet.energy.current) }}</td>
										</tr>
										<tr>
											<td>Производится:</td>
											<td align="right">{{ Format.number(planet.energy.max) }}</td>
										</tr>
										<tr>
											<td>Потребление:</td>
											<td align="right">{{ Format.number(planet.energy.max - planet.energy.current) }}</td>
										</tr>
									</table>
								</div>
							</div>
							<span class="sprite skin_energy"></span>
							<span class="sprite skin_s_energy"></span>
						</div>
						<div title="Доступно энергии">
							<span :class="[planet.energy.current >= 0 ? 'positive' : 'negative']">{{ Format.number(planet.energy.current) }}</span>
						</div>
					</div>
				</div>
				<div class="col-4 text-center">
					<div class="tooltip d-none d-sm-block">
						<div class="tooltip-content">
							<div class="resource-panel-item-tooltip">
								<h1>Аккумулятор</h1>
								<div class="line"></div>
								<table>
									<tr>
										<td>Заряд:</td>
										<td align="right">{{ Format.number(planet.battery.current) }}</td>
									</tr>
									<tr>
										<td>Емкость:</td>
										<td align="right">{{ Format.number(planet.battery.max) }}</td>
									</tr>
									<tr v-if="planet['battery']['tooltip'].length">
										<td colspan="2">{{ planet['battery']['tooltip'] }}</td>
									</tr>
								</table>
							</div>
						</div>
						<img v-if="planet.battery.power > 0 && planet.battery.power < 100" :src="'/assets/images/batt.php?p='+planet.battery.power" width="42" alt="">
						<span v-else :class="'sprite skin_batt'+planet.battery.power"></span>
						<br>
					</div>
					{{ planet.battery.power }}%
				</div>
				<div class="col-4 text-center">
					<a :href="$root.getUrl('credits/')" class="tooltip d-none d-sm-block">
						<div class="tooltip-content">
							<table width="550">
								<tr>
									<td v-for="(time, index) in planet.officiers" align="center" width="14%">
										<div class="separator"></div>
										<span :class="['officier', 'of'+index+(time > ((new Date).getTime() / 1000) ? '_ikon' : '')]"></span>
									</td>
								</tr>
								<tr>
									<td v-for="(time, index) in planet.officiers" align="center">
										<span v-if="time > ((new Date).getTime() / 1000)">Нанят до <font color="lime">{{ date('d.m.Y H:i', time) }}</font></span>
										<span v-else><font color="lime">Не нанят</font></span>
									</td>
								</tr>
							</table>
						</div>
						<span class="sprite skin_kredits"></span>
					</a>
					{{ Format.number(planet.credits) }}
				</div>
			</div>
		</div>
	</div>'
</template>

<script>
	export default {
		name: "application-planet-panel",
		props: ['planet'],
		data: function()
		{
			return {
				updated: 0,
				timer: null
			}
		},
		components: {
			'planet-panel-resource': require('./planet-panel-resource.vue')
		},
		methods:
		{
			update: function ()
			{
				if (typeof this.planet === 'undefined' || this.planet === false)
					return;

				if (this.updated === 0)
					this.updated = (new Date).getTime();

				let factor = ((new Date).getTime() - this.updated) / 1000;

				if (factor < 0)
					return;

				this.updated = (new Date).getTime();

				['metal', 'crystal', 'deuterium'].forEach(function(res)
				{
					if (typeof this.planet[res] === 'undefined')
						return;

					let power = (this.planet[res]['current'] >= this.planet[res]['max']) ? 0 : 1;

					this.planet[res]['current'] += ((this.planet[res]['production'] / 3600) * power * factor);
				}.bind(this));

				this.timer = setTimeout(this.update, 1000);
			}
		},
		created: function ()
		{
			clearTimeout(this.timer);
			this.update();
		},
		updated: function ()
		{
			clearTimeout(this.timer);
			this.update();
		},
		destroyed: function ()
		{
			clearTimeout(this.timer);
		}
	}
</script>