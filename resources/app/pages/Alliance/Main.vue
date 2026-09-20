<template>
	<Head :title="$t('pages.alliance.index.page_title')"/>
	<div class="page-alliance page-alliance-overview">
		<header class="alliance-hero">
			<div class="alliance-emblem"><img v-if="page.image" :src="page.image" :alt="page.name"><AllianceIcon v-else aria-hidden="true"/></div>
			<div class="alliance-identity"><span class="alliance-eyebrow">{{ $t('pages.alliance.index.page_title') }}</span><h1><span class="alliance-tag">[{{ page.tag }}]</span> {{ page.name }}</h1></div>
		</header>
		<div class="alliance-metrics">
			<div><span>{{ $t('pages.alliance.index.members') }}</span><strong>{{ page.members }}</strong></div>
			<div><span>{{ $t('pages.alliance.index.your_rank') }}</span><strong>{{ page.range }}</strong></div>
			<div v-if="page.web"><span>{{ $t('pages.alliance.index.homepage') }}</span><a :href="page.web" target="_blank" rel="noopener noreferrer">{{ page.web }}</a></div>
		</div>
		<UiTabNavigation :items="navigation" :label="$t('pages.alliance.index.page_title')"/>
		<section v-if="page.description" class="alliance-panel"><h2>{{ $t('pages.alliance.ui.about') }}</h2><div class="alliance-prose"><TextViewer :text="page.description"/></div></section>
		<section v-if="page.text" class="alliance-panel"><h2>{{ $t('pages.alliance.index.internal_competence') }}</h2><div class="alliance-prose"><TextViewer :text="page.text"/></div></section>
		<div v-if="!page.owner" class="alliance-footer"><button type="button" class="button is-danger" @click="exit">{{ $t('pages.alliance.index.leave_alliance') }}</button></div>
	</div>
</template>

<script setup>
	import { UiTabNavigation } from '~/components/UI';
	import AllianceIcon from '~/images/icons/alliance.svg?component';
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { Head, useForm } from '@inertiajs/vue3';
	import TextViewer from '~/components/TextViewer.vue';
	import { useI18n } from 'vue-i18n';
	import { openConfirmModal } from '~/composables/useModals.js';
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
	})

	const state = useState();
	const user = computed(() => state.user);
	const navigation = computed(() => [
		{ id: 'members', href: '/alliance/members', label: t('pages.alliance.index.members'), visible: props.page.access.memberlist },
		{ id: 'chat', href: '/alliance/chat', label: t('pages.alliance.chat.meta_title'), count: user.value.alliance?.messages > 0 ? user.value.alliance.messages : undefined, visible: props.page.access.chat },
		{ id: 'diplomacy', href: '/alliance/diplomacy', label: t('pages.alliance.index.diplomacy'), count: props.page.diplomacy > 0 ? props.page.diplomacy : undefined, visible: props.page.diplomacy !== false },
		{ id: 'requests', href: '/alliance/admin/requests', label: t('pages.alliance.index.requests'), count: props.page.requests, visible: props.page.requests > 0 },
		{ id: 'admin', href: '/alliance/admin', label: t('pages.alliance.admin.main_heading'), visible: props.page.access.admin },
	].filter(item => item.visible));

	function exit () {
		openConfirmModal(
			null,
			t('pages.alliance.index.leave_confirm.title'),
			[{
				title: t('pages.alliance.index.leave_confirm.no'),
			}, {
				title: t('pages.alliance.index.leave_confirm.yes'),
				handler() {
					useForm().post('/alliance/exit', {
						preserveUrl: true,
						preserveScroll: true,
						onSuccess() {
							useSuccessNotification(t('pages.alliance.index.leave_confirm.success'));
						}
					});
				}
			}]
		);
	}
</script>