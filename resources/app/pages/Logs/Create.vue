<template>
	<Head :title="$t('pages.logs.create.page_title')"/>
	<div class="block page-logs-create">
		<div class="title logs-create-heading">
			<svg
				viewBox="0 0 24 24"
				fill="none"
				stroke="currentColor"
				stroke-width="1.5"
				stroke-linecap="round"
				stroke-linejoin="round"
				aria-hidden="true"
			>
				<path d="M14 3H5v18h14V8l-5-5Z M14 3v5h5 M8 12h8 M8 16h5"/>
			</svg>
			{{ $t('pages.logs.create.title') }}
		</div>
		<form method="post" @submit.prevent="create">
			<div class="logs-create-fields">
				<div class="logs-create-field">
					<label for="log-title">{{ $t('pages.logs.create.name_label') }}</label>
					<input
						id="log-title"
						type="text"
						name="title"
						v-model="form.title"
						:class="{ error: v$.title.$error }"
						:aria-invalid="v$.title.$error"
						aria-describedby="log-title-hint"
						maxlength="100"
					>
					<p id="log-title-hint">{{ $t('pages.logs.create.name_hint') }}</p>
				</div>
				<div class="logs-create-field">
					<label for="log-code">{{ $t('pages.logs.create.code_label') }}</label>
					<input
						id="log-code"
						type="text"
						name="code"
						v-model="form.code"
						:class="{ error: v$.code.$error }"
						:aria-invalid="v$.code.$error"
						aria-describedby="log-code-hint"
						maxlength="40"
						spellcheck="false"
						autocomplete="off"
					>
					<p id="log-code-hint">{{ $t('pages.logs.create.code_hint') }}</p>
				</div>
			</div>
			<div class="logs-create-actions">
				<button type="submit" class="button" :disabled="form.processing">
					<svg
						viewBox="0 0 24 24"
						fill="none"
						stroke="currentColor"
						stroke-width="1.5"
						stroke-linecap="round"
						stroke-linejoin="round"
						aria-hidden="true"
					>
						<path d="m5 12 4 4L19 6"/>
					</svg>
					{{ $t('pages.logs.create.save') }}
				</button>
				<Link href="/logs" class="logs-create-back">{{ $t('pages.logs.create.back') }}</Link>
			</div>
		</form>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core'
	import { required } from '@vuelidate/validators'
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useUrlSearchParams } from '@vueuse/core';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const params = useUrlSearchParams('history');

	const form = useForm({
		title: '',
		code: params.code || '',
	});

	const validations = {
		title: {
			required,
		},
		code: {
			required,
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function create() {
		if (!await v$.value.$validate()) {
			return;
		}

		form.post('/logs');
	}
</script>