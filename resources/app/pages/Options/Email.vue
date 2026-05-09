<template>
	<Head :title="$t('pages.options.email_change_head_title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.options.email_change_block_title') }}</div>
		<div class="content">
			<template v-for="error in form.errors">
				<div v-html="error" class="th error message"></div>
			</template>
			<form class="block-table text-center" method="post" @submit.prevent="update">
				<div class="grid grid-cols-2">
					<div class="th middle">{{ $t('pages.options.email_change_current_password') }}</div>
					<div class="th middle"><input type="password" name="password" v-model="form.password" :class="{error: v$.password.$error}" size="20"></div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th middle">{{ $t('pages.options.email_change_new_email') }}</div>
					<div class="th middle"><input type="email" name="email" v-model="form.email" :class="{error: v$.email.$error}" size="20" autocomplete="off"></div>
				</div>
				<div>
					<div class="c"><button type="submit" class="button">{{ $t('pages.options.email_change_submit') }}</button></div>
				</div>
			</form>
		</div>
	</div>
	<div class="mt-2">
		<Link href="/options" class="button">{{ $t('pages.options.email_change_back') }}</Link>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core'
	import { required, email as emailValidation } from '@vuelidate/validators'
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
		password: '',
		email: '',
	})

	const validations = {
		password: {
			required
		},
		email: {
			required,
			emailValidation
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function update() {
		if (!await v$.value.$validate()) {
			return
		}

		form.post('/options/email', {
			onSuccess() {
				useSuccessNotification(t('pages.options.email_change_success'));
			}
		});
	}
</script>