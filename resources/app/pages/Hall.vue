<template>
	<Head :title="$t('pages.hall.head_title')"/>
	<div>
		<div class="block-table">
			<div class="grid grid-cols-12">
				<div class="col-span-1 c middle">TOP50</div>
				<div class="col-span-8 c middle">{{ $t('pages.hall.block_heading') }}</div>
				<div class="col-span-3 c middle">
					<select v-model="type">
						<option value="single">{{ $t('pages.hall.type_single') }}</option>
						<option value="team">{{ $t('pages.hall.type_team') }}</option>
					</select>
				</div>
			</div>
		</div>
		<div class="block-table text-center">
			<div v-if="data['items'].length > 0" class="grid grid-cols-12">
				<div class="col-span-1 c">{{ $t('pages.hall.col_place') }}</div>
				<div class="col-span-7 c">
					{{ data['type'] === 'single' ? $t('pages.hall.subtitle_single') : $t('pages.hall.subtitle_team') }}
				</div>
				<div class="col-span-1 c">{{ $t('pages.hall.col_outcome') }}</div>
				<div class="col-span-3 c">{{ $t('pages.hall.col_date') }}</div>
			</div>
			<div v-for="(item, i) in data['items']" class="grid grid-cols-12">
				<div class="col-span-1 th">{{ i + 1 }}</div>
				<div class="col-span-7 th text-left">
					<a v-if="item['report_id']" :href="'/logs/' + item['report_id']" target="_blank">{{ item['title'] }}</a>
					<span v-else>{{ item['title'] }}</span>
				</div>
				<div class="col-span-1 th">
					<template v-if="item['won'] === 0">{{ $t('pages.hall.outcome_loss') }}</template>
					<template v-else-if="item['won'] === 1">{{ $t('pages.hall.outcome_win') }}</template>
					<template v-else>{{ $t('pages.hall.outcome_draw') }}</template>
				</div>
				<div class="col-span-3 th" :class="{ positive: data['last'] === item['id'] }">
					{{ $formatDate(item['date'], 'DD MMM YYYY HH:mm:ss') }}
				</div>
			</div>
			<div v-if="data['items'].length === 0" class="grid">
				<div class="th">{{ $t('pages.hall.empty_list') }}</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { ref, watch } from 'vue';
	import { Head, router } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		data: {
			type: Object,
		}
	});

	const type = ref(props.data['type']);

	watch(type, (value) => {
		router.get('/hall', { type: value });
	});
</script>