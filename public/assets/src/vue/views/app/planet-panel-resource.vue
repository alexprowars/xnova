<template>
	<div class="planet-resource-panel-item">
		<span v-on:click="showPopup" class="tooltip d-none d-sm-block">
			<div class="tooltip-content">
				<planet-panel-resource-tooltip v-bind:resource="resource"></planet-panel-resource-tooltip>
			</div>
			<span v-bind:class="['sprite', 'skin_'+type]"></span>
			<br>
		</span>
		<div class="neutral">{{ resource.title }}</div>
		<div title="Количество ресурса на планете">
			<span v-if="resource.max > resource.current" class="positive">{{ Format.number(resource.current) }}</span>
			<span v-else class="negative">{{ Format.number(resource.current) }}</span>
		</div>
		<span title="Максимальная вместимость хранилищ" class="d-none d-sm-block">
			<span v-if="resource.max > resource.current" class="positive">{{ Format.number(resource.max) }}</span>
			<span v-else class="negative">{{ Format.number(resource.max) }}</span>
		</span>
	</div>',
</template>

<script>
	export default {
		name: "planet-panel-resource",
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