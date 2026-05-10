<template>
	<Head :title="$t('pages.support.index.head_title')"/>
	<div class="page-support">
		<div class="block">
			<div class="title text-center">
				{{ $t('pages.support.index.title') }}
			</div>
			<div class="content">
				<div class="block-table">
					<div v-if="!items.length" class="grid">
						<div class="th">{{ $t('pages.support.index.empty') }}</div>
					</div>
					<div v-else class="grid grid-cols-12">
						<div class="col-span-1 th">{{ $t('pages.support.index.col_id') }}</div>
						<div class="col-span-6 th">{{ $t('pages.support.index.col_subject') }}</div>
						<div class="col-span-2 th">{{ $t('pages.support.index.col_status') }}</div>
						<div class="col-span-3 th">{{ $t('pages.support.index.col_date') }}</div>
					</div>
					<ListItem v-for="item in items" :key="item['id']" :item="item"/>
				</div>
			</div>
		</div>

		<div v-if="!request">
			<div class="separator"></div>
			<div class="grid">
				<div class="text-right">
					<button class="button" @click="newRequest">{{ $t('pages.support.index.create_request') }}</button>
				</div>
			</div>
		</div>
		<CreateTicket v-else @close="request = false"/>
	</div>
</template>

<script setup>
	import CreateTicket from '~/components/Page/Support/Create.vue';
	import { ref } from 'vue';
	import ListItem from '~/components/Page/Support/ListItem.vue';
	import { Head } from '@inertiajs/vue3';

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
	});

	const request = ref(false);

	function newRequest () {
		request.value = !request.value
	}
</script>
