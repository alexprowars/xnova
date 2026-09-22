<template>
	<div class="galaxy-debris-card">
		<div class="galaxy-debris-heading">
			<strong>{{ $t('pages.galaxy.debris') }}</strong>
			<span>[{{ item.position.galaxy }}:{{ item.position.system }}:{{ item.position.planet }}]</span>
		</div>
		<div class="galaxy-debris-content">
			<img class="galaxy-debris-image" src="/assets/images/planeten/debris.jpg" height="64" width="64" alt="">
			<div class="galaxy-debris-resources">
				<div class="galaxy-debris-label">{{ $t('pages.galaxy.debris_resources') }}</div>
				<dl>
					<div v-if="item.debris.metal" class="galaxy-debris-resource">
						<dt><ResourceIcon code="metal" aria-hidden="true"/>{{ $t('resources.metal') }}</dt>
						<dd>{{ $formatNumber(item.debris.metal) }}</dd>
					</div>
					<div v-if="item.debris.crystal" class="galaxy-debris-resource">
						<dt><ResourceIcon code="crystal" aria-hidden="true"/>{{ $t('resources.crystal') }}</dt>
						<dd>{{ $formatNumber(item.debris.crystal) }}</dd>
					</div>
				</dl>
			</div>
		</div>
		<div v-if="!isVacation" class="galaxy-debris-actions">
			<button v-if="canRecycle" type="button" class="button" @click="$emit('collect')">
				{{ $t('pages.galaxy.debris_collect') }}
			</button>
			<Link as="button" type="button" class="button is-secondary"
				:href="'/fleet?galaxy=' + item.position.galaxy + '&system=' + item.position.system + '&planet=' + item.position.planet + '&type=2&mission=8'"
			>
				{{ $t('pages.galaxy.debris_send_fleet') }}
			</Link>
		</div>
	</div>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';
	import ResourceIcon from '~/components/ResourceIcon.vue';

	defineProps({
		item: Object,
		isVacation: Boolean,
		canRecycle: Boolean,
	});

	defineEmits(['collect']);
</script>