<template>
	<Head :title="$t('pages.alliance.index.page_title_no_alliance')"/>
	<div class="page-alliance page-alliance-overview">
		<header class="alliance-heading">
			<h1>{{ $t('pages.alliance.index.page_title_no_alliance') }}</h1>
		</header>
		<div class="alliance-entry-actions">
			<Link href="/alliance/search">
				<AllianceIcon aria-hidden="true"/>
				<span>
					<strong>{{ $t('pages.alliance.search.page_heading') }}</strong>
					<small>{{ $t('pages.alliance.ui.find_hint') }}</small>
				</span>
				<span aria-hidden="true">→</span>
			</Link>
			<Link href="/alliance/create">
				<PlusIcon aria-hidden="true"/>
				<span>
					<strong>{{ $t('pages.alliance.create.title') }}</strong>
					<small>{{ $t('pages.alliance.ui.create_hint') }}</small>
				</span>
				<span aria-hidden="true">→</span>
			</Link>
		</div>
		<section v-if="page.requests.length" class="alliance-panel">
			<h2>
				{{ $t('pages.alliance.ui.your_requests') }}
				<span class="alliance-count">{{ page.requests.length }}</span>
			</h2>
			<div v-for="item in page.requests" :key="item.id" class="alliance-list-row">
				<Link :href="'/alliance/info/' + item.alliance_id"><span class="alliance-tag">[{{ item.tag }}]</span> {{ item.name }}</Link>
				<time>{{ $formatDate(item.date, 'DD MMM YYYY HH:mm') }}</time>
				<button type="button" class="button is-danger" @click="removeRequest(item.id)">
					{{ $t('pages.alliance.ui.withdraw') }}
				</button>
			</div>
		</section>
		<section v-if="page.alliances.length" class="alliance-panel">
			<h2>{{ $t('pages.alliance.ui.top_alliances') }}</h2>
			<div class="alliance-table-wrap">
				<table class="alliance-table">
					<thead>
						<tr>
							<th>{{ $t('pages.alliance.ui.place') }}</th>
							<th>{{ $t('pages.alliance.index.page_title_no_alliance') }}</th>
							<th>{{ $t('pages.alliance.info.label_members') }}</th>
							<th>{{ $t('pages.alliance.members.points') }}</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(item, i) in page.alliances" :key="item.id">
							<td class="alliance-muted">{{ i + 1 }}</td>
							<td>
								<Link :href="'/alliance/info/' + item.id"><span class="alliance-tag">[{{ item.tag }}]</span> {{ item.name }}</Link>
							</td>
							<td>{{ item.members }}</td>
							<td class="alliance-number">{{ item.total_points }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>
	</div>
</template>

<script setup>
	import { useI18n } from 'vue-i18n';

	import AllianceIcon from '~/images/icons/alliance.svg?component';
	import PlusIcon from '~/images/icons/plus.svg?component';
	import { Head, Link, useForm } from '@inertiajs/vue3';
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
	});

	function removeRequest(id) {
		openConfirmModal(
			null,
			t('pages.alliance.ui.withdraw_confirm'),
			[{
				title: t('pages.alliance.admin.confirm_decline'),
			}, {
				title: t('pages.alliance.admin.confirm_accept'),
				handler() {
					useForm().delete('/alliance/request/' + id, {
						preserveUrl: true,
						preserveScroll: true,
						onSuccess() {
							useSuccessNotification(t('pages.alliance.ui.withdrawn'));
						}
					});
				}
			}]
		);
	}
</script>