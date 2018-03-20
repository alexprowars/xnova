<template>
	<div class="resource-panel-item">
		<div @click="showPopup" class="tooltip resource-panel-item-icon">
			<div class="tooltip-content">
				<planet-panel-resource-tooltip :resource="resource" :type="type"></planet-panel-resource-tooltip>
			</div>
			<span class="sprite" :class="['skin_'+type]"></span>
			<span class="sprite" :class="['skin_s_'+type]"></span>
		</div>
		<div class="neutral">{{ $root.getLang('RESOURCES', type) }}</div>
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
		data () {
			return {
				building: {
					metal: 1,
					crystal: 2,
					deuterium: 3,
				}
			}
		},
		methods: {
			showPopup ($event)
			{
				if (typeof $($event.currentTarget).data('tooltipster-ns') !== 'undefined')
					showWindow('', this.$root.getUrl('info/'+this.building[this.type]+'/'));
			}
		}
	}
</script>