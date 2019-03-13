<template>
	<div v-if="resources" class="building-price">
		<div v-if="price.metal > 0" class="building-price-item">
			<img :src="'/images/skin/s_metal.png'" align="absmiddle" v-tooltip="'Металл'" alt="Металл">

			<span v-if="resources.metal.current >= price.metal" class="resYes">{{ price.metal|number }}</span>
			<span v-else="" class="resNo" :v-tooltip="'Необходимо еще: '+$options.filters.number(price.metal - resources.metal.current)">{{ price.metal|number }}</span>
		</div>
		<div v-if="price.crystal > 0" class="building-price-item">
			<img :src="'/images/skin/s_crystal.png'" align="absmiddle" v-tooltip="'Кристалл'" alt="Кристалл">

			<span v-if="resources.crystal.current >= price.crystal" class="resYes">{{ price.crystal|number }}</span>
			<span v-else="" class="resNo" :v-tooltip="'Необходимо еще: '+$options.filters.number(price.crystal - resources.crystal.current)">{{ price.crystal|number }}</span>
		</div>
		<div v-if="price.deuterium > 0" class="building-price-item">
			<img :src="'/images/skin/s_deuterium.png'" align="absmiddle" v-tooltip="'Дейтерий'" alt="Дейтерий">

			<span v-if="resources.deuterium.current >= price.deuterium" class="resYes">{{ price.deuterium|number }}</span>
			<span v-else="" class="resNo" :v-tooltip="'Необходимо еще: '+$options.filters.number(price.deuterium - resources.deuterium.current)">{{ price.deuterium|number }}</span>
		</div>
		<div v-if="price.energy > 0" class="building-price-item">
			<img :src="'/images/skin/s_energy.png'" align="absmiddle" v-tooltip="'Энергия'" alt="Энергия">

			<span v-if="resources.energy.max >= price.energy" class="resYes">{{ price.energy|number }}</span>
			<span v-else="" class="resNo" :v-tooltip="'Необходимо еще: '+$options.filters.number(price.energy - resources.energy.max)">{{ price.energy|number }}</span>
		</div>
	</div>
</template>

<script>
	export default {
		name: "build-row-price",
		props: {
			price: {
				type: Object
			}
		},
		computed: {
			resources () {
				return this.$store.state.resources
			}
		}
	}
</script>