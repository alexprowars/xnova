<template>
	<Head :title="$t('pages.friends.new.page_title')"/>
	<div class="block">
		<div class="title">
			{{ $t('pages.friends.new.title') }}
		</div>
		<div class="content">
			<form class="block-table text-center" @submit.prevent="send">
				<div>
					<div class="th">{{ $t('pages.friends.new.player') }} {{ user['username'] }}</div>
				</div>
				<div>
					<div class="th"><textarea cols="60" rows="10" v-model="message"></textarea></div>
				</div>
				<div>
					<div class="c"><button type="submit" class="button">{{ $t('pages.friends.new.submit') }}</button></div>
				</div>
			</form>
		</div>
	</div>
	<div class="mt-2">
		<Link href="/friends" class="button">{{ $t('pages.friends.new.back') }}</Link>
	</div>
</template>

<script setup>
	import { ref } from 'vue';
	import { Head, Link, router } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { useApiSubmit } from '~/composables/useApi.js';
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
		user: Object,
	})

	const message = ref('');

	function send() {
		useApiSubmit('friends/new/' + props.user['id'], {
			message: message.value,
		}, () => {
			useSuccessNotification(t('pages.friends.new.request_sent'));

			router.visit('/friends');
		});
	}
</script>