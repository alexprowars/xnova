<template>
	<div class="building-price">
		<div v-if="price.metal > 0" class="building-price-item">
			<img :src="$root.getUrl('assets/images/skin/s_metal.png')" align="absmiddle" class="tooltip" data-content="Металл">

			<span v-if="$store.state.resources.metal.current >= price.metal" class="resYes">{{ Format.number(price.metal) }}</span>
			<span v-else="" class="resNo tooltip" :data-content="'Необходимо еще: '+Format.number(price.metal - $store.state.resources.metal.current)">{{ Format.number(price.metal) }}</span>
		</div>
		<div v-if="price.crystal > 0" class="building-price-item">
			<img :src="$root.getUrl('assets/images/skin/s_crystal.png')" align="absmiddle" class="tooltip" data-content="Кристалл">

			<span v-if="$store.state.resources.crystal.current >= price.crystal" class="resYes">{{ Format.number(price.crystal) }}</span>
			<span v-else="" class="resNo tooltip" :data-content="'Необходимо еще: '+Format.number(price.crystal - $store.state.resources.crystal.current)">{{ Format.number(price.crystal) }}</span>
		</div>
		<div v-if="price.deuterium > 0" class="building-price-item">
			<img :src="$root.getUrl('assets/images/skin/s_deuterium.png')" align="absmiddle" class="tooltip" data-content="Дейтерий">

			<span v-if="$store.state.resources.deuterium.current >= price.deuterium" class="resYes">{{ Format.number(price.deuterium) }}</span>
			<span v-else="" class="resNo tooltip" :data-content="'Необходимо еще: '+Format.number(price.deuterium - $store.state.resources.deuterium.current)">{{ Format.number(price.deuterium) }}</span>
		</div>
		<div v-if="price.energy > 0" class="building-price-item">
			<img :src="$root.getUrl('assets/images/skin/s_energy.png')" align="absmiddle" class="tooltip" data-content="Энергия">

			<span v-if="$store.state.resources.energy.max >= price.energy" class="resYes">{{ Format.number(price.energy) }}</span>
			<span v-else="" class="resNo tooltip" :data-content="'Необходимо еще: '+Format.number(price.energy - $store.state.resources.energy.max)">{{ Format.number(price.energy) }}</span>
		</div>
	</div>
</template>

<script>
	export default {
		name: "build-row-price",
		props: ['price'],
	}
</script>