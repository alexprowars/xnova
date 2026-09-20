<template>
	<svg
		viewBox="0 0 24 24"
		fill="none"
		stroke="currentColor"
		stroke-width="1.5"
		stroke-linecap="round"
		stroke-linejoin="round"
		aria-hidden="true"
	>
		<template v-if="type === 'message'">
			<rect x="3" y="5" width="18" height="14" rx="3"/>
			<path d="m3 6 9 7 9-7"/>
		</template>
		<template v-else-if="type === 'friend'">
			<circle cx="9" cy="8" r="3"/>
			<path d="M3 20v-2a6 6 0 0 1 12 0v2m4-13v6m-3-3h6"/>
		</template>
		<template v-else-if="type === 'missile'">
			<path d="M9 15 6 12l4-6 9-3-3 9-6 4-1-1Zm0 0-5 5m3-10-4 1v5m10 1v4l-5-1"/>
			<circle cx="14" cy="8" r="1"/>
		</template>
		<template v-else-if="type === 'spy'">
			<circle cx="12" cy="12" r="8"/>
			<circle cx="12" cy="12" r="3"/>
			<path d="m12 12 7-7M12 1v3M1 12h3m8 8v3m8-11h3"/>
		</template>
		<template v-else-if="type === 'player'">
			<circle cx="12" cy="8" r="4"/>
			<path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
		</template>
		<path v-else-if="type === 'bookmark'" d="M6 3h12v18l-6-4-6 4V3Z"/>
		<template v-else-if="type === 'colonize'">
			<circle cx="10" cy="13" r="7"/>
			<path d="m4 9 5 2 1 4 4 2m4-16v6m-3-3h6"/>
		</template>
		<template v-else-if="type === 'system'">
			<circle cx="12" cy="12" r="3"/>
			<ellipse cx="12" cy="12" rx="11" ry="6" transform="rotate(-35 12 12)"/>
			<path d="M12 2v2m0 16v2"/>
		</template>
		<path v-else-if="type === 'left'" d="m14 5-7 7 7 7"/>
		<path v-else-if="type === 'right'" d="m10 5 7 7-7 7"/>
	</svg>
</template>

<script setup>
	defineProps({
		type: String,
	});
</script>