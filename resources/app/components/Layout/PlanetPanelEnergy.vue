<template>
	<div class="resource-panel-item">
		<InfoPopup :id="4" :title="$t('resources.energy')" class="resource-panel-item-icon">
			<Popper>
				<div>
					<span class="sprite skin_energy"></span>
					<span class="sprite skin_s_energy"></span>
				</div>
				<template #content>
					<div class="resource-panel-item-tooltip">
						<div class="resource-panel-item-tooltip-title">{{ $t('resources.energy') }}</div>
						<div class="resource-panel-item-tooltip-info">
							<div>
								<div>Доступно:</div>
								<div>{{ $formatNumber(resource['value']) }}</div>
							</div>
							<div>
								<div>Производство:</div>
								<div>{{ $formatNumber(resource['capacity']) }}</div>
							</div>
							<div>
								<div>Потребление:</div>
								<div>{{ $formatNumber(resource['capacity'] - resource['value']) }}</div>
							</div>
						</div>
					</div>
				</template>
			</Popper>
		</InfoPopup>
		<div class="neutral">{{ $t('resources.energy') }}</div>
		<div title="Доступно энергии">
			<span :class="[resource['value'] >= 0 ? 'positive' : 'negative']">{{ $formatNumber(resource['value']) }}</span>
		</div>
	</div>
</template>

<script setup>
	import InfoPopup from '../../components/Page/Info/Popup.vue';
	import Popper from '../Popper.vue';

	defineProps({
		resource: {
			type: Object
		},
	});
</script>