<template>
	<Head :title="$t('pages.friends.list.title')"/>
	<div class="block">
		<div class="title">
			{{ $t('pages.friends.list.title') }}
		</div>
		<div class="content">
			<div class="block-table text-center">
				<div class="grid grid-cols-5">
					<div class="c">{{ $t('pages.friends.list.name') }}</div>
					<div class="c">{{ $t('pages.friends.list.alliance') }}</div>
					<div class="c">{{ $t('pages.friends.list.coordinates') }}</div>
					<div class="c">{{ $t('pages.friends.list.online') }}</div>
					<div class="c">&nbsp;</div>
				</div>
				<FriendRow v-for="item in page.items" :key="item['id']" :item="item"/>
				<div v-if="page.items.length === 0" class="grid">
					<div class="th">{{ $t('pages.friends.list.no_friends') }}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="mt-2">
		<Link href="/friends/requests" class="button">{{ $t('pages.friends.list.requests_label') }}</Link>
		<Link href="/friends/requests/my" class="button">{{ $t('pages.friends.list.my_requests') }}</Link>
	</div>
</template>

<script setup>
	import FriendRow from '~/components/Page/Friends/FriendRow.vue';
	import { Head, Link } from '@inertiajs/vue3';

	defineProps({
		page: Object,
	});
</script>