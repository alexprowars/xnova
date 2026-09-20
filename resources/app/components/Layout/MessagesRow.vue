<template>
	<div class="information-message" :class="messageType">
		<svg class="information-message-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<template v-if="messageType === 'warning'">
				<path d="m10.3 4.1-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-2.9l-8-14a2 2 0 0 0-3.4 0Z"/>
				<path d="M12 9v4m0 4h.01"/>
			</template>
			<template v-else-if="messageType === 'success'">
				<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>
			</template>
			<template v-else-if="messageType === 'error'">
				<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6m0-6-6 6"/>
			</template>
			<template v-else>
				<circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10h.01"/>
			</template>
		</svg>
		<div class="information-message-text" v-html="text"></div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';

	const props = defineProps({
		type: {
			type: String,
			default: '',
		},
		text: {
			type: String
		}
	});

	const messageType = computed(() => props.type.replace('-static', ''));
</script>