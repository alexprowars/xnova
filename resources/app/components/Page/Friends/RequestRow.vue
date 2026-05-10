<template>
	<div class="grid grid-cols-6">
		<div class="th middle">
			<Link :href="'/messages/write/' + item['user']['id']">{{ item['user']['name'] }}</Link>
		</div>
		<div class="th middle">
			<Link v-if="item['user']['alliance']['id'] > 0" :href="'/alliance/info/' + item['user']['alliance']['id']">{{ item['user']['alliance']['name'] }}</Link>
			<template v-else>-</template>
		</div>
		<div class="th middle">
			<Link :href="'/galaxy?galaxy=' + item['user']['galaxy'] + '&system=' + item['user']['system']">
				{{ item['user']['galaxy'] }}:{{ item['user']['system'] }}:{{ item['user']['planet'] }}
			</Link>
		</div>
		<div class="col-span-2 th middle" v-html="item['message']"></div>
		<div class="th text-center">
			<button v-if="isMy" @click.prevent="remove" class="button text-danger">{{ $t('pages.friends.requests.remove_request') }}</button>
			<template v-else>
				<button @click.prevent="approve" class="button text-success">{{ $t('pages.friends.requests.approve') }}</button>
				<button @click.prevent="remove" class="button text-danger">{{ $t('pages.friends.requests.reject') }}</button>
			</template>
		</div>
	</div>
</template>

<script setup>
	import { Link, useForm } from '@inertiajs/vue3';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();

	const { item } = defineProps({
		item: Object,
		isMy: {
			type: Boolean,
			default: false,
		},
	});

	function approve () {
		useForm().post('/friends/' + item['id'] + '/approve', {
			preserveUrl: true,
		});
	}

	function remove () {
		openConfirmModal(
			null,
			t('pages.friends.requests.remove_request_confirm.title'),
			[{
				title: t('pages.friends.requests.remove_request_confirm.yes'),
				handler() {
					useForm().delete('/friends/' + item['id'], {
						preserveUrl: true,
						preserveScroll: true,
						onSuccess() {
							useSuccessNotification(t('pages.friends.requests.remove_request_confirm.success'));
						}
					});
				}
			}, {
				title: t('pages.friends.requests.remove_request_confirm.no'),
			}]
		);
	}
</script>