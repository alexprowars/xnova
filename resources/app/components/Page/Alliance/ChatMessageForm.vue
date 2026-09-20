<template>
	<section class="alliance-panel">
		<h2>{{ $t('pages.alliance.ui.write_chat') }}</h2>
		<form class="alliance-form" @submit.prevent="emit('send')">
			<TextEditor v-model="value"/>
			<div v-for="(error, key) in errors" :key="key" class="alliance-errors">{{ error }}</div>
			<div class="alliance-actions">
				<button type="button" class="button is-secondary" :disabled="processing" @click="reset">
					{{ $t('pages.alliance.ui.clear') }}
				</button>
				<button type="submit" class="button" :disabled="processing">
					<SendIcon aria-hidden="true"/>
					{{ $t('pages.alliance.join.submit_request') }}
				</button>
			</div>
		</form>
	</section>
</template>

<script setup>
	import SendIcon from '~/images/icons/send.svg?component';
	import TextEditor from '~/components/TextEditor.vue';

	defineProps({
		processing: Boolean,
		errors: Object,
	});

	const value = defineModel();
	const emit = defineEmits(['send']);

	function reset() {
		value.value = '';
	}
</script>