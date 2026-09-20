<template>
	<Head :title="$t('pages.friends.new.page_title')"/>
	<div class="page-friends">
		<UiBackLink href="/friends">{{ $t('pages.friends.new.back') }}</UiBackLink>
		<UiHeading :title="$t('pages.friends.new.title')"/>
		<UiPanel clip><div class="friend-recipient"><span>{{ $t('pages.friends.new.player') }}</span><Link :href="'/players/' + page.id">{{ page.username }}</Link></div><form class="friends-form ui-form-body" @submit.prevent="send">
			<div class="friends-editor-heading"><label for="friend-message">{{ $t('pages.friends.new.message') }}</label><span>{{ form.message.length }} / 250</span></div>
			<textarea id="friend-message" name="message" rows="6" v-model="form.message" maxlength="250" :class="{error: form.errors.message}" :placeholder="$t('pages.friends.new.message_hint')"/>
			<div v-if="Object.keys(form.errors).length" class="ui-errors" role="alert"><span v-for="(error, field) in form.errors" :key="field">{{ error }}</span></div>
			<div class="ui-actions"><UiButton type="submit" :disabled="form.processing"><SendIcon aria-hidden="true"/>{{ $t('pages.friends.new.submit') }}</UiButton></div>
		</form></UiPanel>
	</div>
</template>

<script setup>
	import { UiBackLink, UiButton, UiHeading, UiPanel } from '~/components/UI';
	import SendIcon from '~/images/icons/send.svg?component';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';

	const { t } = useI18n();

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	});

	const form = useForm({
		message: '',
	});

	function send() {
		if (form.processing) return;

		form.post('/friends/new/' + props.page['id'], {
			preserveUrl: true,
			preserveScroll: true,
			onSuccess() {
				useSuccessNotification(t('pages.friends.new.request_sent'));
			}
		});
	}
</script>