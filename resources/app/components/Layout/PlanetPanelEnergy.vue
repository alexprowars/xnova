<template>
	<div class="resource-panel-item resource-energy">
		<ModalLink navigate href="/info/4" :title="$t('resources.energy')" class="resource-panel-item-icon">
			<Popper>
				<ResourceIcon code="energy" aria-hidden="true" focusable="false"/>
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
		<div class="resource-panel-item-info">
			<div class="resource-panel-item-label">{{ $t('resources.energy') }}</div>
			<div class="resource-panel-item-value" :class="{ 'is-deficit': resource.value < 0 }" title="Доступно энергии">{{ $formatNumber(resource.value) }}</div>
		</div>
	</div>
</template>

<script setup>
	import ResourceIcon from '~/components/ResourceIcon.vue';
	import Popper from '~/components/Popper.vue';
	import { ModalLink } from '@inertiaui/modal-vue';

	defineProps({
		resource: {
			type: Object
		},
	});
</script>