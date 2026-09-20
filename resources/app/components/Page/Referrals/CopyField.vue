<template>
	<div class="referrals-copy-field">
		<label :for="id">{{ label }}</label>
		<div class="referrals-copy-control">
			<input :id="id" type="text" :value="value" readonly @focus="$event.target.select()">
			<button type="button" class="button" @click="copy(value)" :aria-label="$t('pages.referrals.copy_field', { field: label })">
				<svg
					viewBox="0 0 24 24"
					fill="none"
					stroke="currentColor"
					stroke-width="1.6"
					stroke-linecap="round"
					stroke-linejoin="round"
					aria-hidden="true"
				>
					<path v-if="copied" d="m5 12 4 4L19 6"/>
					<path v-else d="M9 9h12v12H9V9ZM5 15H3V3h12v2"/>
				</svg>
				<span aria-live="polite">{{ $t(copied ? 'pages.referrals.copied' : 'pages.referrals.copy') }}</span>
			</button>
		</div>
	</div>
</template>

<script setup>
	import { useClipboard } from '@vueuse/core';

	defineProps({
		id: String,
		label: String,
		value: String,
	});
	const { copy, copied } = useClipboard({ legacy: true, copiedDuring: 2000 });
</script>