<template>
	<Head :title="$t('pages.blocked.meta_title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.blocked.heading') }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div v-if="page.items.length === 0" class="grid">
					<div class="b">{{ $t('pages.blocked.empty_state') }}</div>
				</div>
				<template v-else>
					<div class="grid grid-cols-6">
						<div class="th">{{ $t('pages.blocked.col_player') }}</div>
						<div class="th">{{ $t('pages.blocked.col_block_start') }}</div>
						<div class="th">{{ $t('pages.blocked.col_block_end') }}</div>
						<div class="th col-span-2">{{ $t('pages.blocked.col_reason') }}</div>
						<div class="th">{{ $t('pages.blocked.col_moderator') }}</div>
					</div>
					<div v-for="item in page.items" class="grid grid-cols-6">
						<div class="b">
							<Link :href="'/players/' + item['user']['id']">
								{{ item['user']['name'] }}
							</Link>
						</div>
						<div class="b">
							<div>{{ $formatDate(item['date'], 'DD MMM YYYY') }}</div>
							<div>{{ $formatDate(item['date'], 'HH:mm') }}</div>
						</div>
						<div class="b">
							<div>{{ $formatDate(item['date_end'], 'DD MMM YYYY') }}</div>
							<div>{{ $formatDate(item['date_end'], 'HH:mm:ss') }}</div>
						</div>
						<div class="b col-span-2">{{ item['reason'] }}</div>
						<div class="b">
							<Link :href="'/players/' + item['moderator']['id']">
								{{ item['moderator']['name'] }}
							</Link>
						</div>
					</div>
					<div class="grid">
						<div class="b">{{ $t('pages.blocked.footer_total', { count: page.items.length }) }}</div>
					</div>
				</template>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { Head, Link } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	});
</script>