<template>
	<article class="friend-request">
		<div class="friend-request-heading"><div class="friend-request-identity"><SendMessagePopup :id="item.user.id" class="friend-name" :title="$t('pages.friends.write_message')">{{ item.user.name }}<SendIcon aria-hidden="true"/></SendMessagePopup><Link v-if="item.user.alliance.id > 0" :href="'/alliance/info/' + item.user.alliance.id" class="friend-alliance">{{ item.user.alliance.name }}</Link><Link :href="'/galaxy?galaxy=' + item.user.galaxy + '&system=' + item.user.system" class="friend-coordinates">[{{ item.user.galaxy }}:{{ item.user.system }}:{{ item.user.planet }}]</Link></div><div class="friend-request-actions"><UiButton variant="danger" v-if="isMy" :disabled="form.processing" @click="remove">{{ $t('pages.friends.requests.remove_request') }}</UiButton><template v-else><UiButton variant="success" :disabled="form.processing" @click="approve">{{ $t('pages.friends.requests.approve') }}</UiButton><UiButton variant="danger" :disabled="form.processing" @click="remove">{{ $t('pages.friends.requests.reject') }}</UiButton></template></div></div>
		<div class="friend-request-message" v-html="item.message || '—'"/>
	</article>
</template>

<script setup>
	import { UiButton } from '~/components/UI';
	import SendMessagePopup from '~/components/Page/Messages/SendMessagePopup.vue';
	import SendIcon from '~/images/icons/send.svg?component';
	import { Link, useForm } from '@inertiajs/vue3';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();
	const form = useForm({});

	const { item } = defineProps({
		item: Object,
		isMy: {
			type: Boolean,
			default: false,
		},
	});

	function approve () {
		if (form.processing) return;

		form.post('/friends/' + item['id'] + '/approve', {
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
					form.delete('/friends/' + item['id'], {
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