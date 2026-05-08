<template>
	<Head :title="$t('pages.logs.index.page_title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.logs.index.title') }}</div>
		<div class="content">
			<div class="block-table text-center">
				<div class="grid grid-cols-12">
					<div class="col-span-1 c">{{ $t('pages.logs.index.number') }}</div>
					<div class="col-span-7 c">{{ $t('pages.logs.index.name') }}</div>
					<div class="col-span-2 c">{{ $t('pages.logs.index.link') }}</div>
					<div class="col-span-2 c">{{ $t('pages.logs.index.management') }}</div>
				</div>
				<ItemRow v-for="row in items" :key="row['id']" :item="row"/>
				<div v-if="items.length === 0" class="grid">
					<div class="th text-center">{{ $t('pages.logs.index.no_logs') }}</div>
				</div>
				<div class="grid">
					<div class="c">
						<Link href="/logs/create" class="button">{{ $t('pages.logs.index.add_new') }}</Link>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import ItemRow from '../../components/Page/Logs/ItemRow.vue';
	import { Head, Link } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	})
</script>