<template>
	<Head :title="$t('pages.alliance.create.title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.alliance.create.title') }}</div>
		<div class="content">
			<form @submit.prevent="create" class="block-table text-center">
				<div class="grid grid-cols-2">
					<div class="th middle">{{ $t('pages.alliance.create.tag_label') }}</div>
					<div class="th middle">
						<input type="text" name="tag" :class="{error: v$.tag.$error}" size="8" maxlength="8" v-model="form.tag">
					</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th middle">{{ $t('pages.alliance.create.name_label') }}</div>
					<div class="th middle">
						<input type="text" name="name" :class="{error: v$.name.$error}" size="20" maxlength="30" v-model="form.name">
					</div>
				</div>
				<div class="grid">
					<div class="c">
						<button type="submit" class="button">{{ $t('pages.alliance.create.submit') }}</button>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="mt-2">
		<Link href="/alliance" class="button">{{ $t('pages.alliance.create.back') }}</Link>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const { t } = useI18n();

	const form = useForm({
		name: '',
		tag: '',
	});

	const validations = {
		name: {
			required
		},
		tag: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function create() {
		if (!await v$.value.$validate()) {
			return
		}

		form.post('/alliance/create', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.create.created'));
			}
		});
	}
</script>