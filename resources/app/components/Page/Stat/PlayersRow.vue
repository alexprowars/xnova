<template>
	<div class="page-stat-players-row grid grid-cols-12">
		<div class="th sm:col-span-1 col-span-2">
			{{ item['place'] }}
			<div class="sm:hidden">
				<div v-if="item['diff'] === 0" :style="{color: '#87CEEB'}">*</div>
				<span v-else-if="item['diff'] < 0" class="negative">{{ item['diff'] }}</span>
				<span v-else-if="item['diff'] > 0" class="positive">+{{ item['diff'] }}</span>
			</div>
		</div>
		<div class="th sm:col-span-1 hidden sm:block">
			<div v-if="item['diff'] === 0" :style="{color: '#87CEEB'}">*</div>
			<span v-else-if="item['diff'] < 0" class="negative">{{ item['diff'] }}</span>
			<span v-else-if="item['diff'] > 0" class="positive">+{{ item['diff'] }}</span>
		</div>
		<div class="th sm:col-span-4 col-span-5">
			<ModalLink navigate :href="'/players/' + item['id']">
				<span :class="{ neutral: marked }">{{ item['name'] }}</span>
			</ModalLink>
			<div v-if="item['alliance']" class="sm:hidden">
				<Link :class="{neutral: item['alliance']['marked']}" :href="'/alliance/info/' + item['alliance']['id']">
					{{ item['alliance']['name'] }}
				</Link>
			</div>
			<div v-else class="sm:hidden">
				&nbsp;
			</div>
		</div>
		<div class="th sm:col-span-1 col-span-2 middle">
			<img v-if="item['race']" :src="'/assets/images/skin/race' + item['race'] + '.gif'" width="16" height="16" style="margin-right:7px;">

			<SendMessagePopup v-if="user" :title="$t('send_message')" :id="item['id']"/>
		</div>
		<div class="th sm:col-span-3 hidden sm:block row-alliance">
			<Link v-if="item['alliance']" :class="{ neutral: item['alliance']['marked'] }" :href="'/alliance/info/' + item['alliance']['id']">
				{{ item['alliance']['name'] }}
			</Link>
			<div v-else>
				&nbsp;
			</div>
		</div>
		<div class="th sm:col-span-2 col-span-3 middle">
			<Link :href="'/players/' + item['id'] + '/stats'">
				{{ $formatNumber(item['points']) }}
			</Link>
		</div>
	</div>
</template>

<script setup>
	import SendMessagePopup from '../Messages/SendMessagePopup.vue';
	import { Link, usePage } from '@inertiajs/vue3';
	import { computed } from 'vue';
	import { useUrlSearchParams } from '@vueuse/core';
	import { ModalLink } from '@inertiaui/modal-vue';

	const props = defineProps({
		item: Object,
	});

	const page = usePage();
	const user = computed(() => page.props.user);
	const query = useUrlSearchParams('history');

	const marked = computed(() => {
		let id = parseInt(query.id || 0);

		return (!id && user.value?.['id'] === props.item['id']) || id === props.item['id'];
	});
</script>
