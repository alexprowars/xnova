<template>
	<tr>
		<td>
			<SendMessagePopup :id="item.user.id" class="friend-name" :title="$t('pages.friends.write_message')">
				{{ item.user.name }}
				<SendIcon aria-hidden="true"/>
			</SendMessagePopup>
		</td>
		<td>
			<Link v-if="item.user.alliance.id > 0" :href="'/alliance/info/' + item.user.alliance.id">{{ item.user.alliance.name }}</Link>
			<span v-else class="friends-muted">—</span>
		</td>
		<td>
			<Link :href="'/galaxy?galaxy=' + item.user.galaxy + '&system=' + item.user.system" class="friend-coordinates">
				[{{ item.user.galaxy }}:{{ item.user.system }}:{{ item.user.planet }}]
			</Link>
		</td>
		<td>
			<span class="friend-status" :class="'online-' + item.online">
				{{ $t(item.online === 1 ? 'pages.friends.list.in_game' : item.online === 2 ? 'pages.friends.list.15_min' : 'pages.friends.list.not_in_game') }}
			</span>
		</td>
		<td class="friend-remove">
			<UiButton
				variant="danger"
				:disabled="form.processing"
				@click="remove"
				:title="$t('pages.friends.list.remove')"
				:aria-label="$t('pages.friends.list.remove')"
			>
				<TrashIcon aria-hidden="true"/>
			</UiButton>
		</td>
	</tr>
</template>

<script setup>
	import { UiButton } from '~/components/UI';
	import SendMessagePopup from '~/components/Page/Messages/SendMessagePopup.vue';
	import TrashIcon from '~/images/icons/trash.svg?component';
	import SendIcon from '~/images/icons/send.svg?component';
	import { Link, useForm } from '@inertiajs/vue3';
	import { openConfirmModal } from '~/composables/useModals.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();
	const form = useForm({});

	const { item } = defineProps({
		item: Object,
	});

	function remove () {
		openConfirmModal(
			null,
			t('pages.friends.list.remove_confirm.title'),
			[{
				title: t('pages.friends.list.remove_confirm.yes'),
				handler() {
					form.delete('/friends/' + item.id, {
						preserveUrl: true,
						preserveScroll: true,
						onSuccess() {
							useSuccessNotification(t('pages.friends.list.remove_confirm.success'));
						}
					});
				}
			}, {
				title: t('pages.friends.list.remove_confirm.no'),
			}]
		);
	}
</script>