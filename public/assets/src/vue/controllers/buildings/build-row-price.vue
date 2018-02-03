<template>
	<div class="building-price">
		<div v-if="price.metal > 0" class="building-price-item">
			<img :src="$root.getUrl('assets/images/skin/s_metal.png')" align="absmiddle" class="tooltip" data-content="Металл">
			<span :class="[$store.state.resources.metal.current >= price.metal ? 'resYes' : 'resNo']">{{ Format.number(price.metal) }}</span>
		</div>
		<div v-if="price.crystal > 0" class="building-price-item">
			<img :src="$root.getUrl('assets/images/skin/s_crystal.png')" align="absmiddle" class="tooltip" data-content="Кристалл">
			<span :class="[$store.state.resources.crystal.current >= price.crystal ? 'resYes' : 'resNo']">{{ Format.number(price.crystal) }}</span>
		</div>
		<div v-if="price.deuterium > 0" class="building-price-item">
			<img :src="$root.getUrl('assets/images/skin/s_deuterium.png')" align="absmiddle" class="tooltip" data-content="Дейтерий">
			<span :class="[$store.state.resources.deuterium.current >= price.deuterium ? 'resYes' : 'resNo']">{{ Format.number(price.deuterium) }}</span>
		</div>
		<div v-if="price.energy > 0" class="building-price-item">
			<img :src="$root.getUrl('assets/images/skin/s_energy.png')" align="absmiddle" class="tooltip" data-content="Энергия">
			<span :class="[$store.state.resources.energy.max >= price.energy ? 'resYes' : 'resNo']">{{ Format.number(price.energy) }}</span>
		</div>
	</div>
</template>

<script>
	export default {
		name: "build-row-price",
		props: ['price'],
	}
</script>