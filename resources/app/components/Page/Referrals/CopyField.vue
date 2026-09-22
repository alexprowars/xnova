<template>
	<div class="referrals-copy-field">
		<label :for="id">{{ label }}</label>
		<div class="referrals-copy-control">
			<input :id="id" type="text" :value="value" readonly @focus="$event.target.select()">
			<button type="button" class="button" @click="copy(value)" :aria-label="$t('pages.referrals.copy_field', { field: label })">
				<component :is="copied ? CheckIcon : CopyIcon" aria-hidden="true"/>
				<span aria-live="polite">{{ $t(copied ? 'pages.referrals.copied' : 'pages.referrals.copy') }}</span>
			</button>
		</div>
	</div>
</template>

<script setup>
	import CheckIcon from '~/images/icons/check.svg?component';
	import CopyIcon from '~/images/icons/copy.svg?component';
	import { useClipboard } from '@vueuse/core';

	defineProps({
		id: String,
		label: String,
		value: String,
	});
	const { copy, copied } = useClipboard({ legacy: true, copiedDuring: 2000 });
</script>