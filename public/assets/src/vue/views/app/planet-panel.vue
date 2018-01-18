<template>
	<div class="row topnav">
		<div class="col-md-6 col-sm-6 col-xs-12">
			<div class="row">
				<div class="col-xs-4 text-xs-center"><planet-panel-resource v-bind:type="'metal'" v-bind:resource="planet.metal"></planet-panel-resource></div>
				<div class="col-xs-4 text-xs-center"><planet-panel-resource v-bind:type="'crystal'" v-bind:resource="planet.crystal"></planet-panel-resource></div>
				<div class="col-xs-4 text-xs-center"><planet-panel-resource v-bind:type="'deuterium'" v-bind:resource="planet.deuterium"></planet-panel-resource></div>
			</div>
		</div>
		<div class="col-md-6 col-sm-6 col-xs-12">
			<div class="row">
				<div class="col-xs-4 text-xs-center">
					<span onclick="showWindow('', '/info/4/')" title="Солнечная батарея" class="hidden-xs-down"><span class="sprite skin_energie"></span><br></span>
					<div class="neutral">Энергия</div>
					<div title="Энергетический баланс">
						<span v-if="planet.energy.current >= 0" class="positive">{{ Format.number(planet.energy.current) }}</span>
						<span v-else class="negative">{{ Format.number(planet.energy.current) }}</span>
					</div>
					<span title="Выработка энергии" class="hidden-xs-down positive">{{ Format.number(planet.energy.max) }}</span>
				</div>
				<div class="col-xs-4 text-xs-center">
					<span class="tooltip hidden-xs-down">
						<div class="tooltip-content"><center>Вместимость:<br>{{ Format.number(planet.battery.current) }} / {{ Format.number(planet.battery.max) }} <br> {{ planet.battery.tooltip }}</center></div>
						<img v-if="planet.battery.power > 0 && planet.battery.power < 100" v-bind:src="'/assets/images/batt.php?p='+planet.battery.power" width="42" alt="">
						<span v-else v-bind:class="'sprite skin_batt'+planet.battery.power"></span>
						<br>
					</span>
					<div class="neutral">Аккумулятор</div>
					{{ planet.battery.power }}%<br>
				</div>
				<div class="col-xs-4 text-xs-center">
					<a v-bind:href="$root.getUrl('credits/')" class="tooltip hidden-xs-down">
						<div class="tooltip-content">
							<table width=550>
								<tr>
									<td v-for="(time, index) in planet.officiers" align="center" width="14%">'+
										<div class="separator"></div>
										<span v-bind:class="['officier', 'of'+index+(time > ((new Date).getTime() / 1000) ? '_ikon' : '')]"></span>
									</td>
								</tr>
								<tr>
									<td v-for="(time, index) in planet.officiers" align="center">'+
										<span v-if="time > ((new Date).getTime() / 1000)">Нанят до <font color=lime>{{ date('d.m.Y H:i', time) }}</font></span>
										<span v-else><font color=lime>Не нанят</font></span>
									</td>
								</tr>
							</table>
						</div>
						<span class="sprite skin_kredits"></span><br>
					</a>
					<div class="neutral">Кредиты</div>
					{{ Format.number(planet.credits) }}<br>
				</div>
			</div>
		</div>
	</div>'
</template>

<script>
	export default {
		name: "planet-panel",
		props: ['planet'],
		components: {
			'planet-panel-resource': require('./planet-panel-resource.vue')
		},
		methods:
		{
			update: function ()
			{
				if (typeof this.planet === 'undefined' || this.planet === false)
					return;

				if (XNova.lastUpdate === 0)
					XNova.lastUpdate = (new Date).getTime();

				var factor = ((new Date).getTime() - XNova.lastUpdate) / 1000;

				if (factor < 0)
					return;

				XNova.lastUpdate = (new Date).getTime();

				['metal', 'crystal', 'deuterium'].forEach(function(res)
				{
					if (typeof this.planet[res] === 'undefined')
						return;

					var power = (this.planet[res]['current'] >= this.planet[res]['max']) ? 0 : 1;

					this.planet[res]['current'] += ((this.planet[res]['production'] / 3600) * power * factor);
				}.bind(this));
			}
		},
		created: function ()
		{
			this.update();

			clearInterval(timeouts['res_count']);
			timeouts['res_count'] = setInterval(this.update, 1000);
		},
		updated: function ()
		{
			this.update();

			clearInterval(timeouts['res_count']);
			timeouts['res_count'] = setInterval(this.update, 1000);
		},
		destroyed: function ()
		{
			clearInterval(timeouts['res_count']);
		}
	}
</script>