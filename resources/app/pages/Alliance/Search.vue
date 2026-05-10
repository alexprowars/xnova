<template>
	<Head :title="$t('pages.alliance.search.page_heading')"/>
	<div>
		<div class="block">
			<div class="title">
				{{ $t('pages.alliance.search.page_heading') }}
			</div>
			<div class="content">
				<form class="block-table text-center" method="post" @submit.prevent="search">
					<div>
						<div class="th">
							<input type="text" name="query" :class="{error: v$.query.$error}" v-model="form.query">
						</div>
					</div>
					<div>
						<div class="c">
							<button type="submit" class="button">{{ $t('pages.alliance.search.submit_action') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>

		<div v-if="page.items.length" class="block">
			<div class="title">
				{{ $t('pages.alliance.search.results_section_heading') }}
			</div>
			<div class="content">
				<div class="block-table text-center">
					<div v-for="r in page.items" class="grid grid-cols-3">
						<div class="th">
							<Link :href="'/alliance/join/' + r['id']">
								[{{ r['tag'] }}]
							</Link>
						</div>
						<div class="th">
							{{ r['name'] }}
						</div>
						<div class="th">
							{{ r['members'] }}
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="mt-2">
			<Link href="/alliance" class="button">{{ $t('pages.alliance.search.back_link') }}</Link>
		</div>
	</div>
</template>

<script setup>
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
		if (!await v$.value.$validate()) {
			return
		}

		form.post('/alliance/search');
	}
</script>