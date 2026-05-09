<template>
	<div class="resource-panel-item">
		<ModalLink navigate href="info/4" :title="$t('resources.energy')" class="resource-panel-item-icon">
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
		</ModalLink>
		<div class="neutral">{{ $t('resources.energy') }}</div>
		<div title="Доступно энергии">
			<span :class="[resource['value'] >= 0 ? 'positive' : 'negative']">{{ $formatNumber(resource['value']) }}</span>
		</div>
	</div>
</template>

<script setup>
	import Popper from '~/components/Popper.vue';
	import { ModalLink } from '@inertiaui/modal-vue';

	defineProps({
		resource: {
			type: Object
		},
	});
</script>