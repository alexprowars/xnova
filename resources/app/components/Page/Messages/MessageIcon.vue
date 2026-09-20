<template>
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="paths[name]"/></svg>
</template>

<script setup>
	defineProps({ name: String });
	const paths = {
		mail: 'M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm-1 1 9 7 9-7',
		reply: 'm9 5-6 6 6 6M3 11h10a7 7 0 0 1 7 7v1',
		quote: 'M4 5h6v7H4V5Zm10 0h6v7h-6V5ZM10 12c0 4-2 6-5 7m15-7c0 4-2 6-5 7',
		flag: 'M5 21V3m0 1c5-4 9 4 14 0v10c-5 4-9-4-14 0',
		trash: 'M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7',
		send: 'm21 3-7 18-4-7-7-4 18-7ZM10 14 21 3',
		user: 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM4 21v-2a8 8 0 0 1 16 0v2',
		back: 'm10 5-7 7 7 7M3 12h18',
	};
</script>