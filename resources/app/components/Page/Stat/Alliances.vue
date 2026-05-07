<template>
	<div class="block page-stat-alliances">
		<div class="content">
			<div class="block-table text-center">
				<div class="grid grid-cols-12">
					<div class="c col-span-2 sm:col-span-1">{{ $t('pages.stats.alliances_table_rank') }}</div>
					<div class="c sm:col-span-1 hidden sm:block">{{ $t('pages.stats.alliances_table_delta') }}</div>
					<div class="c col-span-4 sm:col-span-5">{{ $t('pages.stats.alliances_table_name') }}</div>
					<div class="c col-span-2 sm:col-span-1">{{ $t('pages.stats.alliances_table_members') }}</div>
					<div class="c sm:col-span-2 hidden sm:block">{{ $t('pages.stats.alliances_table_points') }}</div>
					<div class="c sm:col-span-2 hidden sm:block">{{ $t('pages.stats.alliances_table_points_per_member') }}</div>
					<div class="c col-span-4 sm:hidden">{{ $t('pages.stats.alliances_table_points_mobile') }}</div>
				</div>
				<div v-for="item in items" class="page-stat-alliances-row grid grid-cols-12">
					<div class="col-span-2 sm:col-span-1 th">
						{{ item['place'] }}
						<div class="sm:hidden">
							<div v-if="item['diff'] === 0" :style="{color: '#87CEEB'}">*</div>
							<span v-else-if="item['diff'] < 0" class="negative">{{ item['diff'] }}</span>
							<span v-else-if="item['diff'] > 0" class="positive">+{{ item['diff'] }}</span>
						</div>
					</div>
					<div class="sm:col-span-1 th hidden sm:block">
						<div v-if="item['diff'] === 0" :style="{color: '#87CEEB'}">*</div>
						<span v-else-if="item['diff'] < 0" class="negative">{{ item['diff'] }}</span>
						<span v-else-if="item['diff'] > 0" class="positive">+{{ item['diff'] }}</span>
					</div>
					<div class="col-span-4 sm:col-span-5 th middle">
						<Link :class="{neutral: item['name_marked']}" :href="'/alliance/info/' + item['id']">{{ item['name'] }}</Link>
					</div>
					<div class=" col-span-2 sm:col-span-1 th middle">
						{{ item['members'] }}
					</div>
					<div class="sm:col-span-2 th hidden sm:block">
						<Link :href="'/alliance/stat/' + item['id']">{{ $formatNumber(item['points']) }}</Link>
					</div>
					<div class="sm:col-span-2 th hidden sm:block">
						{{ $formatNumber(Math.floor(item['points'] / item['members'])) }}
					</div>
					<div class="col-span-4 th sm:hidden">
						<Link :href="'/alliance/stat/' + item['id']">{{ $formatNumber(item['points']) }}</Link>
						<br>
						{{ $formatNumber(Math.floor(item['points'] / item['members'])) }}
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { Link } from '@inertiajs/vue3';

	defineProps({
		items: Array
	});
</script>
