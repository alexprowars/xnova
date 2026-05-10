<template>
	<Head :title="$t('pages.alliance.index.page_title')"/>
	<div class="block">
		<div class="title">
			{{ $t('pages.alliance.index.title') }}
		</div>
		<div class="content">
			<div class="block-table text-center">
				<div v-if="page['image']">
					<div class="th">
						<img :src="page['image']" class="max-w-full" alt="">
					</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.index.abbreviation') }}</div>
					<div class="th">{{ page['tag'] }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.index.name') }}</div>
					<div class="th">{{ page['name'] }}</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.index.members') }}</div>
					<div class="th">
						{{ page['members'] }}
						<template v-if="page['access']['memberlist']">
							(<Link href="/alliance/members">{{ $t('pages.alliance.index.members_list') }}</Link>)
						</template>
					</div>
				</div>
				<div class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.index.your_rank') }}</div>
					<div class="th">
						{{ page['range'] }}
						<template v-if="page['access']['admin']">
							(<Link href="/alliance/admin">{{ $t('pages.alliance.index.alliance_management') }}</Link>)
						</template>
					</div>
				</div>
				<div v-if="page['diplomacy'] !== false" class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.index.diplomacy') }}</div>
					<div class="th">
						<Link href="/alliance/diplomacy">{{ $t('pages.alliance.index.view') }}</Link>
						<template v-if="page['diplomacy'] > 0">
							({{ page['diplomacy'] }} {{ $t('pages.alliance.index.new_requests') }})
						</template>
					</div>
				</div>
				<div v-if="page['requests'] > 0" class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.index.requests') }}</div>
					<div class="th">
						<Link href="/alliance/admin/requests">{{ page['requests'] }} {{ $t('pages.alliance.index.requests_count') }}</Link>
					</div>
				</div>
				<div v-if="page['access']['chat']" class="grid grid-cols-2">
					<div class="th">
						{{ $t('pages.alliance.index.alliance_chat') }}
						<template v-if="user.alliance?.messages > 0">
							({{ user.alliance.messages }} {{ $t('pages.alliance.index.new') }})
						</template>
					</div>
					<div class="th"><Link href="/alliance/chat">{{ $t('pages.alliance.index.enter_chat') }}</Link></div>
				</div>
				<div v-if="page['web']" class="grid grid-cols-2">
					<div class="th">{{ $t('pages.alliance.index.homepage') }}</div>
					<div class="th"><a :href="page['web']" target="_blank">{{ page['web'] }}</a></div>
				</div>
				<div v-if="page['description']">
					<div class="b p-1 h-60">
						<TextViewer :text="page['description']"/>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div v-if="page['text']" class="block">
		<div class="title">
			{{ $t('pages.alliance.index.internal_competence') }}
		</div>
		<div class="content">
			<div class="b p-1 min-h-32">
				<TextViewer :text="page['text']"/>
			</div>
		</div>
	</div>
	<div v-if="!page['owner']" class="block">
		<div class="title">
			{{ $t('pages.alliance.index.leave_alliance') }}
		</div>
		<div class="content">
			<div class="th text-center">
				<button class="button" @click.prevent="exit">{{ $t('pages.alliance.index.continue') }}</button>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
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

	defineProps({
		page: Object,
	})

	const state = useState();
	const user = computed(() => state.user);

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
