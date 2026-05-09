<template>
	<div @click.prevent="setActive" class="buldings-list-item" :class="[!available ? 'disabled' : '']">
		<img :src="'/assets/images/elements/' + item['id'] + '.webp'" :alt="item['name']">
		<div class="name">
			{{ item['name'] }}
		</div>
		<div class="level">
			{{ level }}
		</div>
		<div v-if="inQueue" class="upgrade active">
			<IconUpgrade/>
		</div>
		<div v-else-if="available && user['queue_max'] > queueByType('build').length" class="upgrade" @click.prevent.stop="emit('build', item['id'])">
			<IconUpgrade/>
		</div>
	</div>
</template>

<script setup>
	import IconUpgrade from '~/images/icons/upgrade.svg?component';
	import { computed } from 'vue';
	import { usePage } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { queueByType, emptyFieldsCount } from '~/utils/buildings.js';

	const props = defineProps({
		item: {
			tyoe: Object,
		}
	});

	const { tm } = useI18n();
	const page = usePage();
	const user = computed(() => page.props.user);
	const planet = computed(() => page.props.planet);
	const emit = defineEmits(['select', 'build']);

	const level = computed(() => planet.value['buildings'][props['item']['code']] || 0);

	const hasResources = computed(() => {
		return Object.keys(tm('resources')).every(res => {
			if (typeof props.item.price[res] !== 'undefined' && typeof planet.value['resources'][res] !== 'undefined' && props.item.price[res] > 0) {
				if (res === 'energy') {
					if (planet.value['resources'][res].capacity < props.item.price[res]) {
						return false
					}
				} else if (planet.value['resources'][res].value < props.item.price[res]) {
					return false
				}
			}

			return true
		})
	});

	const available = computed(() => {
		return props.item['available'] && hasResources.value && emptyFieldsCount.value > 0 && !user.value.vacation;
	});

	const inQueue = computed(() => {
		return queueByType('build').filter((item) => item['item'] === props.item['id']).length > 0;
	})

	function setActive() {
		emit('select');
	}
</script>