<template>
	<Head :title="$t('pages.alliance.create.title')"/>
	<div class="page-alliance ">
		<AllianceBack/>
		<header class="alliance-heading"><h1>{{ $t('pages.alliance.create.title') }}</h1></header>
		<section class="alliance-panel"><form class="alliance-form" @submit.prevent="create">
			<div class="alliance-fields">
				<label>{{ $t('pages.alliance.create.tag_label') }}<input type="text" name="tag" :class="{error: v$.tag.$error}" maxlength="8" v-model="form.tag"><span v-if="v$.tag.$error" class="alliance-errors">{{ $t('pages.alliance.ui.required') }}</span></label>
				<label>{{ $t('pages.alliance.ui.name_label') }}<input type="text" name="name" :class="{error: v$.name.$error}" maxlength="30" v-model="form.name"><span v-if="v$.name.$error" class="alliance-errors">{{ $t('pages.alliance.ui.required') }}</span></label>
			</div>
			<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
			<div class="alliance-actions"><button type="submit" class="button" :disabled="form.processing">{{ $t('pages.alliance.create.submit') }}</button></div>
		</form></section>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import { Head, useForm } from '@inertiajs/vue3';
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
		if (form.processing) return;

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