<template>
	<Head :title="$t('pages.alliance.admin.requests_head_title')"/>
	<div class="page-alliance page-alliance-admin">
		<AllianceBack href="/alliance/admin"/>
		<header class="alliance-heading">
			<h1>
				{{ $t('pages.alliance.admin.requests_page_heading') }}
				<span class="alliance-count">{{ page.items.length }}</span>
			</h1>
		</header>
		<RequestAcceptForm v-if="request" :key="request.id" :request="request" @close="request = null"/>
		<section class="alliance-panel">
			<div v-if="!page.items.length" class="alliance-empty">{{ $t('pages.alliance.admin.requests_empty_list') }}</div>
			<button
				v-for="item in page.items"
				:key="item.id"
				type="button"
				class="alliance-request-button"
				:class="{ 'is-active': request?.id === item.id }"
				:aria-expanded="request?.id === item.id"
				@click="show(item)"
			>
				<strong>{{ item.name }}</strong>
				<time>{{ $formatDate(item.date, 'DD MMM YYYY HH:mm') }}</time>
				<span>{{ $t('pages.alliance.ui.review') }} →</span>
			</button>
		</section>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import { ref } from 'vue';
	import RequestAcceptForm from '~/components/Page/Alliance/RequestAcceptForm.vue';
	import { Head } from '@inertiajs/vue3';

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

	const request = ref(null);

	function show(req) {
		request.value = req;
	}
</script>