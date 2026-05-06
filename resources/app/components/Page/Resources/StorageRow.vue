<template>
	<div class="grid grid-cols-12">
		<div class="col-span-2 th">
			{{ $t('resources.' + resource) }}
		</div>
		<div class="col-span-1 th text-center">
			{{ storage }}%
		</div>
		<div class="col-span-9 th text-center">
			<ResourcesBar :value="storage"/>
		</div>
	</div>
</template>

<script setup>
	import ResourcesBar from '~/components/Page/Resources/Bar.vue';
	import { computed } from 'vue';
	import { usePage } from '@inertiajs/vue3';

	const props = defineProps({
		resource: String,
	});

	const page = usePage();
	const planet = computed(() => page.props.planet);

	const storage = computed(() => Math.max(0, Math.floor((planet.value['resources'][props.resource]['value'] / planet.value['resources'][props.resource]['capacity']) * 100)));
</script>