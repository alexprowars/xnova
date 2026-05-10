<template>
	<div class="grid grid-cols-5">
		<div class="th middle">
			<Link :href="'/messages/write/' + item['user']['id']">{{ item['user']['name'] }}</Link>
		</div>
		<div class="th middle">
			<Link v-if="item['user']['alliance']['id'] > 0" :href="'/alliance/info/' + item['user']['alliance']['id']">
				{{ item['user']['alliance']['name'] }}
			</Link>
			<template v-else>-</template>
		</div>
		<div class="th middle">
			<Link :href="'/galaxy?galaxy=' + item['user']['galaxy']+'&system=' + item['user']['system']">
				{{ item['user']['galaxy'] }}:{{ item['user']['system'] }}:{{ item['user']['planet'] }}
			</Link>
		</div>
		<div class="th middle">
			<span v-if="item['online'] === 1" class="positive">
				{{ $t('pages.friends.list.in_game') }}
			</span>
			<span v-if="item['online'] === 2" class="neutral">
				{{ $t('pages.friends.list.15_min') }}
			</span>
			<span v-else class="negative">
				{{ $t('pages.friends.list.not_in_game') }}
			</span>
		</div>
		<div class="th middle">
			<button @click.prevent="remove" class="button text-danger">
				{{ $t('pages.friends.list.remove') }}
			</button>
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
	});

	function remove () {
		openConfirmModal(
			null,
			t('pages.friends.list.remove_confirm.title'),
			[{
				title: t('pages.friends.list.remove_confirm.yes'),
				handler() {
					useForm().delete('/friends/' + item.id, {
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