<template>
	<div class="overContent">
		<div v-if="price.metal > 0">
			<img v-bind:src="$root.getUrl('assets/images/skin/s_metal.png')" align="absmiddle" class="tooltip" data-content="Металл">
			<span v-bind:class="[$store.state.resources.metal.current >= price.metal ? 'resYes' : 'resNo']">{{ Format.number(price.metal) }}</span>
		</div>
		<div v-if="price.crystal > 0">
			<img v-bind:src="$root.getUrl('assets/images/skin/s_crystal.png')" align="absmiddle" class="tooltip" data-content="Кристалл">
			<span v-bind:class="[$store.state.resources.crystal.current >= price.crystal ? 'resYes' : 'resNo']">{{ Format.number(price.crystal) }}</span>
		</div>
		<div v-if="price.deuterium > 0">
			<img v-bind:src="$root.getUrl('assets/images/skin/s_deuterium.png')" align="absmiddle" class="tooltip" data-content="Дейтерий">
			<span v-bind:class="[$store.state.resources.deuterium.current >= price.deuterium ? 'resYes' : 'resNo']">{{ Format.number(price.deuterium) }}</span>
		</div>
	</div>
</template>

<script>
	export default {
		name: "build-row-price",
		props: ['price'],
	}
</script>