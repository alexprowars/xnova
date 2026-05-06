<template>
	<Head :title="$t('pages.records.page_title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.records.heading') }}</div>
		<div class="content">
			<div class="block-table">
				<div class="grid">
					<div class="th text-center">
						{{ $t('pages.records.updated_at', { time: $formatDate(update, 'DD MMM YYYY HH:mm:ss') }) }}
					</div>
				</div>
				<template v-for="(list, group) in items">
					<div class="grid grid-cols-12">
						<div class="col-span-5 c">{{ group }}</div>
						<div class="col-span-5 c text-center">{{ $t('pages.records.col_player') }}</div>
						<div class="col-span-2 c text-center">{{ $t('pages.records.col_level') }}</div>
					</div>
					<div v-for="(info, building) in list" class="grid grid-cols-12">
						<div class="col-span-5 th text-left">{{ building }}</div>
						<div class="col-span-5 th text-center">{{ info['winner'] }}</div>
						<div class="col-span-2 th text-center">{{ info['count'] }}</div>
					</div>
				</template>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		update: String,
		items: Object,
	});
</script>