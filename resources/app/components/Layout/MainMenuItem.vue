<template>
	<li>
		<Link :href="item.url" :title="item.title" :class="{ active: page.url.startsWith(item.url) }" :aria-current="page.url.startsWith(item.url) ? 'page' : undefined">
			<svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="icons[item.url]"/></svg>
			<span>{{ item.title }}</span>
		</Link>
	</li>
</template>

<script setup>
	import { Link, usePage } from '@inertiajs/vue3';

	defineProps({
		item: {
			type: Object
		}
	});

	const page = usePage();

	const icons = {
		'/overview': 'M3 10 12 3l9 7v11h-6v-7H9v7H3Z',
		'/empire': 'M3 21V9h6v12M9 21V3h6v18M15 21V12h6v9',
		'/galaxy': 'M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM3 18 21 6M5 5c-5 5 9 19 14 14S10 0 5 5Z',
		'/fleet': 'm12 3 9 18-9-4-9 4 9-18Zm0 0v14',
		'/buildings': 'M3 21V10l6 3V7l6 3V3h6v18ZM7 17h1m4 0h1m4 0h1',
		'/research': 'M9 3h6m-5 0v7L4 20h16l-6-10V3M8 15h8',
		'/shipyard': 'M5 3h14v13H5ZM2 16h20l-4 5H6ZM12 3v13',
		'/defense': 'm12 3 8 3v6c0 5-8 9-8 9s-8-4-8-9V6Zm-4 9 3 3 5-6',
		'/resources': 'm12 3 9 5v9l-9 5-9-5V8Zm-9 5 9 5 9-5M12 13v9',
		'/merchant': 'M3 7h17l-4-4m5 14H4l4 4M3 7v6m18 4v-6',
		'/officiers': 'm12 3 3 6 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1Z',
		'/alliance': 'M8 7a4 4 0 1 0 8 0 4 4 0 0 0-8 0ZM4 21v-3a8 8 0 0 1 16 0v3',
		'/friends': 'M3 21v-3a6 6 0 0 1 12 0v3M6 6a3 3 0 1 0 6 0 3 3 0 0 0-6 0Zm11-2a3 3 0 0 1 0 6m1 4a5 5 0 0 1 3 5v2',
		'/notes': 'M5 3h14v18H5ZM8 8h8m-8 4h8m-8 4h5',
		'/records': 'M4 21V11h4v10m2 0V3h4v18m2 0v-6h4v6',
		'/hall': 'M8 3h8v7a4 4 0 0 1-8 0ZM8 5H3v3a4 4 0 0 0 5 4m8-7h5v3a4 4 0 0 1-5 4m-4 2v7m-4 0h8',
		'/logs': 'M4 4h16v16H4Zm4 4h8m-8 4h8m-8 4h5',
	};
</script>