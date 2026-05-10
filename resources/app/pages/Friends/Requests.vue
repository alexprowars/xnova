<template>
	<div class="friends page-friends-request">
		<div class="block">
			<div class="title">
				{{ isMy ? $t('pages.friends.list.my_requests') : $t('pages.friends.requests.other_requests') }}
			</div>
			<div class="content">
				<div class="block-table text-center">
					<div class="grid grid-cols-6">
						<div class="c">{{ $t('pages.friends.list.name') }}</div>
						<div class="c">{{ $t('pages.friends.list.alliance') }}</div>
						<div class="c">{{ $t('pages.friends.list.coordinates') }}</div>
						<div class="col-span-2 c">{{ $t('pages.friends.list.text') }}</div>
						<div class="c">&nbsp;</div>
					</div>
					<RequestRow v-for="item in items" :key="item['id']" :item="item" :is-my="isMy"/>
					<div v-if="items.length === 0" class="grid">
						<div class="th">{{ $t('pages.friends.requests.no_requests') }}</div>
					</div>
				</div>
			</div>
		</div>
		<div class="mt-2">
			<Link href="/friends" class="button">{{ $t('pages.friends.requests.back') }}</Link>
		</div>
	</div>
</template>

<script setup>
	import RequestRow from '~/components/Page/Friends/RequestRow.vue';
	import { computed } from 'vue';
	import { Link, usePage } from '@inertiajs/vue3';

	const page = usePage();

	const isMy = computed(() => page.url.indexOf('/my') !== -1);

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	});
</script>