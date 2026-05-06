<template>
	<div @click="setActive" class="buldings-list-item" :class="[!available ? 'disabled' : '']">
		<img :src="'/assets/images/elements/' + item['id'] + '.webp'" :alt="item['name']">
		<div class="name">
			{{ item['name'] }}
		</div>
		<div class="level">
			{{ $formatNumber(level) }}
		</div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { usePage } from '@inertiajs/vue3';

	const props = defineProps({
		item: {
			tyoe: Object,
		}
	});

	const page = usePage();
	const user = computed(() => page.props.user);
	const planet = computed(() => page.props.planet);
	const emit = defineEmits(['select', 'build']);

	const level = computed(() => planet.value['units'][props['item']['code']] || 0);

	const available = computed(() => {
		return props.item['available'] && !user.value.vacation;
	});

	function setActive() {
		emit('select');
	}
</script>