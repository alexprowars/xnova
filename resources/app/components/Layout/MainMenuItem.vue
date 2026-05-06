<template>
	<li>
		<Link :href="item.url" :class="{ active: page.url.startsWith(item.url) }">{{ item.title }}</Link>
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
</script>