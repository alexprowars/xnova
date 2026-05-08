<template>
	<Head :title="$t('pages.logs.create.page_title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.logs.create.title') }}</div>
		<div class="content">
			<form method="post" @submit.prevent="create" class="block-table text-center">
				<div class="grid">
					<div class="th">
						<div>{{ $t('pages.logs.create.name_label') }}</div>
						<div class="mt-1">
							<input type="text" name="title" v-model="form.title" :class="{error: v$.title.$error}" size="50" maxlength="100">
						</div>
						<div class="mt-4">{{ $t('pages.logs.create.code_label') }}</div>
						<div class="mt-1">
							<input type="text" name="code" v-model="form.code" :class="{error: v$.code.$error}" size="50" maxlength="40">
						</div>
					</div>
				</div>
				<div class="grid">
					<div class="c">
						<button type="submit">{{ $t('pages.logs.create.save') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="mt-2">
		<Link href="/logs" class="button">{{ $t('pages.logs.create.back') }}</Link>
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