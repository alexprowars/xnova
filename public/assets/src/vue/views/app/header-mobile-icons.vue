<template>
	<div class="icon-panel d-sm-none">
		<a v-bind:href="$root.getUrl('stat/')" class="sprite ico_stats"></a>
		<a v-bind:href="$root.getUrl('tech/')" class="sprite ico_tech"></a>
		<a v-bind:href="$root.getUrl('sim/')" class="sprite ico_sim"></a>
		<a v-bind:href="$root.getUrl('search/')" class="sprite ico_search"></a>
		<a v-bind:href="$root.getUrl('support/')" class="sprite ico_support"></a>
		<a href="http://forum.xnova.su/" target="_blank" class="sprite ico_forum"></a>
		<a v-bind:href="$root.getUrl('options/')" class="sprite ico_settings"></a>
		<a v-bind:href="$root.getUrl('logout/')" class="sprite ico_exit"  data-link="Y"></a>
</div>
</template>

<script>
	export default {
		name: "application-header-mobile-icons"
	}
</script>