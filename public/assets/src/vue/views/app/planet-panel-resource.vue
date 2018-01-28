<template>
	<div class="resource-panel-item">
		<div v-on:click="showPopup" class="tooltip">
			<div class="tooltip-content">
				<planet-panel-resource-tooltip :resource="resource"></planet-panel-resource-tooltip>
			</div>
			<span class="sprite" :class="['skin_'+type]"></span>
			<span class="sprite" :class="['skin_s_'+type]"></span>
		</div>
		<div title="Количество ресурса на планете">
			<span :class="[resource.max > resource.current ? 'positive' : 'negative']">
				{{ Format.number(resource.current) }}
			</span>
		</div>
	</div>
</template>

<script>
	export default {
		name: "application-planet-panel-resource",
		props: ['resource', 'type'],
		components: {
			'planet-panel-resource-tooltip': require('./planet-panel-resource-tooltip.vue')
		},
		methods:
		{
			showPopup: function ()
			{
				showWindow('', this.$root.getUrl(this.resource.url))
			}
		}
	}
</script>