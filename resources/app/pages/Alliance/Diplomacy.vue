<template>
	<Head :title="$t('pages.alliance.diplomacy.page_title')"/>
	<div class="page-alliance page-alliance-diplomacy">
		<AllianceBack/>
		<header class="alliance-heading"><h1>{{ $t('pages.alliance.diplomacy.page_title') }}</h1></header>

		<section v-if="page.DMyQuery.length" class="alliance-panel"><h2>{{ $t('pages.alliance.diplomacy.my_requests') }}<span class="alliance-count">{{ page.DMyQuery.length }}</span></h2>
			<div v-for="item in page.DMyQuery" :key="item.id" class="alliance-relation"><strong>{{ item.name }}</strong><span class="alliance-relation-status" :class="'relation-' + item.type">{{ $t('alliance.diplomacy_status.' + item.type) }}</span><div class="alliance-relation-actions">
				<button type="button" class="button is-danger icon-button" :disabled="relationForm.processing" @click="reject(item.id)" :title="$t('pages.alliance.diplomacy.delete_request')" :aria-label="$t('pages.alliance.diplomacy.delete_request') + ': ' + item.name"><TrashIcon aria-hidden="true"/></button>
			</div></div>
		</section>

		<section v-if="page.DQuery.length" class="alliance-panel"><h2>{{ $t('pages.alliance.diplomacy.requests_to_alliance') }}<span class="alliance-count">{{ page.DQuery.length }}</span></h2>
			<div v-for="item in page.DQuery" :key="item.id" class="alliance-relation"><strong>{{ item.name }}</strong><span class="alliance-relation-status" :class="'relation-' + item.type">{{ $t('alliance.diplomacy_status.' + item.type) }}</span><div class="alliance-relation-actions">
				<button type="button" class="button is-success" :disabled="relationForm.processing" @click="accept(item.id)">{{ $t('pages.alliance.diplomacy.confirm') }}</button>
				<button type="button" class="button is-danger icon-button" :disabled="relationForm.processing" @click="reject(item.id)" :title="$t('pages.alliance.diplomacy.delete_request')" :aria-label="$t('pages.alliance.diplomacy.delete_request') + ': ' + item.name"><TrashIcon aria-hidden="true"/></button>
			</div></div>
		</section>

		<section  class="alliance-panel"><h2>{{ $t('pages.alliance.diplomacy.alliance_relations') }}<span class="alliance-count">{{ page.DText.length }}</span></h2>
			<div v-for="item in page.DText" :key="item.id" class="alliance-relation"><strong>{{ item.name }}</strong><span class="alliance-relation-status" :class="'relation-' + item.type">{{ $t('alliance.diplomacy_status.' + item.type) }}</span><div class="alliance-relation-actions">
				<button type="button" class="button is-danger icon-button" :disabled="relationForm.processing" @click="reject(item.id)" :title="$t('pages.alliance.diplomacy.delete_request')" :aria-label="$t('pages.alliance.diplomacy.delete_request') + ': ' + item.name"><TrashIcon aria-hidden="true"/></button>
			</div></div>
			<div v-if="!page.DText.length" class="alliance-empty">{{ $t('pages.alliance.ui.no_relations') }}</div>
		</section>
		<div v-for="(error, key) in relationForm.errors" :key="key" class="alliance-errors">{{ error }}</div>
		<DiplomacyCreate :items="page.items"/>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import TrashIcon from '~/images/icons/trash.svg?component';
	import DiplomacyCreate from '~/components/Page/Alliance/DiplomacyCreate.vue';
	import { Head, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();
	const relationForm = useForm({ id: null });

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	})

	function accept(id) {
		relationForm.id = id;
		relationForm.post('/alliance/diplomacy/accept', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.diplomacy.relation_confirmed'));
			}
		});
	}

	function reject(id) {
		relationForm.id = id;
		relationForm.post('/alliance/diplomacy/reject', {
			onSuccess() {
				useSuccessNotification(t('pages.alliance.diplomacy.relation_terminated'));
			}
		});
	}
</script>