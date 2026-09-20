<template>
	<Head :title="$t('pages.alliance.search.page_heading')"/>
	<div class="page-alliance ">
		<AllianceBack/>
		<header class="alliance-heading">
			<h1>{{ $t('pages.alliance.search.page_heading') }}</h1>
		</header>
		<section class="alliance-panel">
			<form class="alliance-form" @submit.prevent="search">
				<label for="alliance-query">{{ $t('pages.alliance.ui.search_label') }}</label>
				<div class="alliance-search-field">
					<input id="alliance-query" type="search" name="query" :class="{error: v$.query.$error}" v-model="form.query">
					<button type="submit" class="button" :disabled="form.processing">
						{{ $t('pages.alliance.search.submit_action') }}
					</button>
				</div>
				<div v-if="v$.query.$error" class="alliance-errors">{{ $t('pages.alliance.ui.required') }}</div>
				<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
			</form>
		</section>
		<section v-if="page.items.length" class="alliance-panel">
			<h2>
				{{ $t('pages.alliance.search.results_section_heading') }}
				<span class="alliance-count">{{ page.items.length }}</span>
			</h2>
			<div v-for="item in page.items" :key="item.id" class="alliance-list-row">
				<Link :href="'/alliance/info/' + item.id"><span class="alliance-tag">[{{ item.tag }}]</span> {{ item.name }}</Link>
				<span class="alliance-muted">{{ $t('pages.alliance.info.label_members') }}: {{ item.members }}</span>
				<Link :href="'/alliance/join/' + item.id" class="button is-secondary">{{ $t('pages.alliance.info.button_join') }}</Link>
			</div>
		</section>
	</div>
</template>

<script setup>
	import AllianceBack from '~/components/Page/Alliance/Back.vue';
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import { Head, Link, useForm } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		page: Object,
	});

	const form = useForm({
		query: '',
	});

	const validations = {
		query: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function search() {
		if (form.processing) return;

		if (!await v$.value.$validate()) {
			return
		}

		form.post('/alliance/search');
	}
</script>