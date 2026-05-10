<template>
	<div @click="setActive" class="buldings-list-item" :class="[!available ? 'disabled' : '']">
		<img :src="'/assets/images/elements/' + item['id'] + '.webp'" :alt="item['name']">
		<div class="name">
			{{ item['name'] }}
		</div>
		<div class="level">
			{{ level }}
		</div>
		<div v-if="typeof item['build'] === 'object'" class="upgrade active">
			<IconUpgrade/>
		</div>
		<div v-else-if="available && !queueByType('tech').length" class="upgrade" @click.prevent.stop="emit('build')">
			<IconUpgrade/>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import IconUpgrade from '~/images/icons/upgrade.svg?component';
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { queueByType } from '~/utils/buildings.js';

	const props = defineProps({
		item: {
			type: Object,
		}
	});

	const { tm } = useI18n();
	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);
	const emit = defineEmits(['select', 'build']);

	const level = computed(() => user.value['technology'][props['item']['code']] || 0);

	const hasResources = computed(() => {
		return Object.keys(tm('resources')).every(res => {
			return !(typeof props.item.price[res] !== 'undefined' && planet.value['resources'][res] !== 'undefined' && props.item.price[res] > 0
				&& planet.value['resources'][res] && planet.value['resources'][res].value < props.item.price[res]);
		})
	});

	const available = computed(() => {
		return props.item['available'] && hasResources.value && !user.value.vacation;
	});

	function setActive() {
		emit('select');
	}
</script>