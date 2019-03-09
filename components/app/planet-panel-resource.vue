<template>
	<div class="resource-panel-item">
		<div @click="showPopup" class="tooltip resource-panel-item-icon">
			<div class="tooltip-content">
				<no-ssr>
					<resource-tooltip :resource="resource" :type="type"></resource-tooltip>
				</no-ssr>
			</div>
			<span class="sprite" :class="['skin_'+type]"></span>
			<span class="sprite" :class="['skin_s_'+type]"></span>
		</div>
		<div class="neutral">{{ $t('RESOURCES.'+type+'') }}</div>
		<div title="Количество ресурса на планете">
			<span :class="[resource.max > resource.current ? 'positive' : 'negative']">
				{{ resource.current|number }}
			</span>
		</div>
	</div>
</template>

<script>
	import ResourceTooltip from './planet-panel-resource-tooltip.vue'

	export default {
		name: "planet-panel-resource",
		props: ['resource', 'type'],
		components: {
			ResourceTooltip
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
				{
					this.$get('/info/'+this.building[this.type]+'/', {
						popup: 'Y'
					})
					.then((data) =>
					{
						let component = this.$router.getMatchedComponents(data.url)

						if (component.length)
						{
							this.$modal.show(component[0], {
								popup: data.page
							}, {
								width: 600,
								height: 'auto'
							})
						}
					})
				}
			}
		}
	}
</script>