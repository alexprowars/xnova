<template>
	<Head :title="$t('pages.alliance.diplomacy.page_title')"/>
	<div>
		<div v-if="data['DMyQuery'].length > 0" class="block">
			<div class="title">{{ $t('pages.alliance.diplomacy.my_requests') }}</div>
			<div class="content">
				<div class="block-table text-center">
					<div v-for="item in data['DMyQuery']" class="grid grid-cols-3">
						<div class="th">{{ item['name'] }}</div>
						<div class="th">{{ $t('alliance.diplomacy_status.' + item['type']) }}</div>
						<div class="th">
							<a href="" @click.prevent="reject(item['id'])"><img src="/assets/images/abort.gif" :alt="$t('pages.alliance.diplomacy.delete_request')"></a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div v-if="data['DQuery'].length > 0" class="block">
			<div class="title">{{ $t('pages.alliance.diplomacy.requests_to_alliance') }}</div>
			<div class="content">
				<div class="block-table text-center">
					<div v-for="item in data['DQuery']" class="grid grid-cols-3">
						<div class="th">{{ item['name'] }}</div>
						<div class="th">{{ $t('alliance.diplomacy_status.' + item['type']) }}</div>
						<div class="th">
							<a href="" @click.prevent="accept(item['id'])"><img src="/assets/images/appwiz.gif" :alt="$t('pages.alliance.diplomacy.confirm')"></a>
							<a href="" @click.prevent="reject(item['id'])"><img src="/assets/images/abort.gif" :alt="$t('pages.alliance.diplomacy.delete_request')"></a>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="block">
			<div class="title">{{ $t('pages.alliance.diplomacy.alliance_relations') }}</div>
			<div class="content">
				<div class="block-table text-center">
					<div v-for="item in data['DText']" class="grid grid-cols-3">
						<div class="th">{{ item['name'] }}</div>
						<div class="th">{{ $t('alliance.diplomacy_status.' + item['type']) }}</div>
						<div class="th">
							<a href="" @click.prevent="reject(item['id'])"><img src="/assets/images/abort.gif" :alt="$t('pages.alliance.diplomacy.delete_request')"></a>
						</div>
					</div>
					<div v-if="data['DText'].length === 0">
						<div class="th">{{ $t('pages.alliance.diplomacy.none') }}</div>
					</div>
				</div>
			</div>
		</div>

		<DiplomacyCreate :items="data['items']"/>

		<div class="mt-2">
			<Link href="/alliance" class="button">{{ $t('pages.alliance.diplomacy.back') }}</Link>
		</div>
	</div>
</template>

<script setup>
	import DiplomacyCreate from '~/components/Page/Alliance/DiplomacyCreate.vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		data: Object,
	})

	function accept(id) {
		useForm({ id }).post('/alliance/diplomacy/accept', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.diplomacy.relation_confirmed'));
			}
		});
	}

	function reject(id) {
		useForm({ id }).post('/alliance/diplomacy/reject', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.diplomacy.relation_terminated'));
			}
		});
	}
</script>