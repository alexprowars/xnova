<template>
	<section class="message-compose">
		<header class="message-compose-heading"><MessageIcon name="mail"/><h1>{{ $t('pages.messages.form.title') }}</h1></header>
		<form method="post" @submit.prevent="send">
			<div class="message-recipient"><span class="message-recipient-icon"><MessageIcon name="user"/></span><div><span class="message-field-label">{{ $t('pages.messages.form.recipient') }}</span><div class="message-recipient-name" v-html="to || '—'"/></div></div>
			<div class="message-compose-editor">
				<div class="message-field-label">{{ $t('pages.messages.form.message') }}</div>
				<TextEditor :class="{error: v$.message.$error || form.errors.message}" v-model="form.message"/>
				<div v-if="v$.message.$error || Object.keys(form.errors).length" class="message-form-errors" role="alert">
					<span v-if="v$.message.$error">{{ $t('pages.messages.form.required') }}</span>
					<span v-for="(error, field) in form.errors" :key="field">{{ error }}</span>
				</div>
			</div>
			<div class="message-compose-footer"><button type="submit" class="button" :disabled="form.processing || id <= 0"><MessageIcon name="send"/>{{ $t('pages.messages.form.submit') }}</button></div>
		</form>
	</section>
</template>

<script setup>
	import MessageIcon from '~/components/Page/Messages/MessageIcon.vue';
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import TextEditor from '~/components/TextEditor.vue';
	import { useForm } from '@inertiajs/vue3';
	import { useModal } from '@inertiaui/modal-vue';

	const props = defineProps({
		id: {
			type: Number,
			default: 0,
		},
		to: {
			type: String,
			default: '',
		},
		message: {
			type: String,
			default: '',
		}
	});

	const form = useForm({
		message: props.message,
	});

	const validations = {
		message: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	const modal = useModal();

	async function send () {
		if (props.id <= 0 || form.processing) {
			return;
		}

		if (!await v$.value.$validate()) {
			return;
		}

		form.post('/messages/write/' + props.id, {
			preserveUrl: true,
			onSuccess: () => {
				form.resetAndClearErrors();
				v$.value.$reset();
				modal?.close();
			}
		});
	}
</script>