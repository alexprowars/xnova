<template>
	<div class="block">
		<div class="title">
			{{ $t('pages.messages.form.title') }}
		</div>
		<div class="content">
			<form action="" method="post" @submit.prevent="send" class="block-table form-group text-center">
				<div class="grid">
					<div class="th">
						{{ $t('pages.messages.form.recipient') }}
					</div>
				</div>
				<div v-if="to.length" class="grid">
					<div class="c" v-html="to"></div>
				</div>
				<div class="grid">
					<div class="th">
						<TextEditor :class="{error: v$.message.$error}" v-model="message"/>
					</div>
				</div>
				<div class="grid">
					<div class="c">
						<button type="submit">{{ $t('pages.messages.form.submit') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import { ref } from 'vue';
	import TextEditor from '../../TextEditor.vue';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '../../../composables/useToast.js';

	const { t } = useI18n();

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

	const message = ref(props.message);
	const error = ref(false);

	const validations = {
		message: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		{ message },
		{ $autoDirty: true }
	);

	async function send () {
		if (props.id <= 0) {
			return;
		}

		if (!await v$.value.$validate()) {
			return
		}

		try {
			const result = await useApiPost('/messages/write/' + props.id, {
				message: message.value,
			});

			if (result.redirect && result.redirect.length) {
				window.location.href = result.redirect;
			} else {
				useSuccessNotification(t('pages.messages.form.sent'));

				message.value = '';
				v$.value.$reset();

				error.value = result.error;
			}
		} catch {
		}
	}
</script>