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
						<TextEditor :class="{error: v$.message.$error}" v-model="form.message"/>
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
	import TextEditor from '~/components/TextEditor.vue';
	import { useForm } from '@inertiajs/vue3';

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

	async function send () {
		if (props.id <= 0) {
			return;
		}

		if (!await v$.value.$validate()) {
			return
		}

		form.post('/messages/write/' + props.id, {
			onSuccess: () => {
				form.resetAndClearErrors();
				v$.value.$reset();
			}
		});
	}
</script>