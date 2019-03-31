<template>
	<div v-if="planet" class="row resource-panel">
		<div class="col-md-6 col-sm-6 col-12">
			<div class="row">
				<div class="col-4 text-center"><panel-resource :type="'metal'" :resource="planet.metal"></panel-resource></div>
				<div class="col-4 text-center"><panel-resource :type="'crystal'" :resource="planet.crystal"></panel-resource></div>
				<div class="col-4 text-center"><panel-resource :type="'deuterium'" :resource="planet.deuterium"></panel-resource></div>
			</div>
		</div>
		<div class="col-md-6 col-sm-6 col-12">
			<div class="row">
				<div class="col-4 text-center">
					<div class="resource-panel-item">
						<popup-link :to="'/info/4/'" title="Солнечная батарея" class="resource-panel-item-icon">
							<Popper>
								<no-ssr>
									<div class="resource-panel-item-tooltip">
										<h1>Энергия</h1>
										<div class="line"></div>
										<table>
											<tr>
												<td>Доступно:</td>
												<td align="right">{{ planet['energy']['current']|number }}</td>
											</tr>
											<tr>
												<td>Производится:</td>
												<td align="right">{{ planet['energy']['max']|number }}</td>
											</tr>
											<tr>
												<td>Потребление:</td>
												<td align="right">{{ (planet['energy']['max'] - planet['energy']['current']) | number }}</td>
											</tr>
										</table>
									</div>
								</no-ssr>
								<template slot="reference">
									<span class="sprite skin_energy"></span>
									<span class="sprite skin_s_energy"></span>
								</template>
							</Popper>
						</popup-link>
						<div class="neutral">{{ $t('RESOURCES.energy') }}</div>
						<div title="Доступно энергии">
							<span :class="[planet['energy']['current'] >= 0 ? 'positive' : 'negative']">{{ planet['energy']['current']|number }}</span>
						</div>
					</div>
				</div>
				<div class="col-4 text-center">
					<div class="resource-panel-item">
						<Popper class="d-sm-inline-block resource-panel-item-icon">
							<no-ssr>
								<div class="resource-panel-item-tooltip">
									<h1>Аккумулятор</h1>
									<div class="line"></div>
									<table>
										<tr>
											<td>Заряд:</td>
											<td align="right">{{ planet['battery']['current']|number }}</td>
										</tr>
										<tr>
											<td>Емкость:</td>
											<td align="right">{{ planet['battery']['max']|number }}</td>
										</tr>
										<tr v-if="planet['battery']['tooltip'].length">
											<td colspan="2">{{ planet['battery']['tooltip'] }}</td>
										</tr>
									</table>
								</div>
							</no-ssr>
							<template slot="reference">
								<img v-if="planet['battery']['power'] > 0 && planet['battery']['power'] < 100" :src="'/api/battery.php?p='+planet['battery']['power']" width="42" alt="">
								<span v-else="" class="sprite" :class="['skin_batt'+planet['battery']['power']]"></span>
								<br>
							</template>
						</Popper>
						<div class="neutral">Аккумулятор</div>
						{{ planet['battery']['power'] }}%
					</div>
				</div>
				<div class="col-4 text-center">
					<div class="resource-panel-item">
						<nuxt-link to="/credits/" class="d-sm-inline-block resource-panel-item-icon">
							<Popper>
								<no-ssr>
									<table width="550">
										<tr>
											<td v-for="(time, index) in planet['officiers']" align="center" width="14%">
												<div class="separator"></div>
												<span :class="['officier', 'of'+index+(time > ((new Date).getTime() / 1000) ? '_ikon' : '')]"></span>
											</td>
										</tr>
										<tr>
											<td v-for="time in planet['officiers']" align="center">
												<span v-if="time > $store.getters.getServerTime()">Нанят до <font color="lime">{{ time | date('d.m.Y H:i') }}</font></span>
												<span v-else><font color="lime">Не нанят</font></span>
											</td>
										</tr>
									</table>
								</no-ssr>
								<template slot="reference">
									<span class="sprite skin_kredits"></span>
								</template>
							</Popper>
						</nuxt-link>
						<div class="neutral">Кредиты</div>
						{{ planet['credits']|number }}
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	import PanelResource from './planet-panel-resource.vue'

	export default {
		name: "planet-panel",
		components: {
			PanelResource
		},
		data () {
			return {
				updated: 0,
				timer: null
			}
		},
		computed: {
			planet () {
				return this.$store.state.resources || false;
			}
		},
		methods:
		{
			update ()
			{
				if (typeof this.planet === 'undefined' || this.planet === false)
					return;

				if (this.updated === 0)
					this.updated = (new Date).getTime();

				let factor = ((new Date).getTime() - this.updated) / 1000;

				if (factor < 0)
					return;

				this.updated = (new Date).getTime();
				let resources = {};

				['metal', 'crystal', 'deuterium'].forEach((res) =>
				{
					if (typeof this.planet[res] === 'undefined')
						return;

					let power = (this.planet[res]['current'] >= this.planet[res]['max']) ? 0 : 1;

					resources[res] = this.planet[res]['current'] + ((this.planet[res]['production'] / 3600) * power * factor);
				});

				if (Object.keys(resources).length > 0)
					this.$store.commit('setPlanetResources', resources)

				this.timer = setTimeout(this.update, 1000);
			}
		},
		created ()
		{
			clearTimeout(this.timer);
			this.update();
		},
		updated ()
		{
			clearTimeout(this.timer);
			this.update();
		},
		destroyed () {
			clearTimeout(this.timer);
		}
	}
</script>