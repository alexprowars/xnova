<template>
	<Head :title="$t('menu.officiers')"/>
	<div class="page-officiers">
		<div class="block officiers-credits">
			<div class="title">{{ $t('menu.officiers') }}</div>
			<div class="content officiers-credits-content is-padded">
				<div class="officiers-credits-icon"><ResourceIcon code="credits" aria-hidden="true" focusable="false"/></div>
				<div class="officiers-credits-description">
					<strong>{{ $t('pages.officiers.credits', [$formatNumber(user.credits)]) }}</strong>
					<p>{{ $t('pages.officiers.credits_info') }}</p>
					<p class="officiers-credits-hint">{{ $t('pages.officiers.credits_info_2') }}</p>
				</div>
				<Link href="/credits" class="button">{{ $t('pages.officiers.get_credits') }}</Link>
			</div>
		</div>
		<div class="officiers-list">
			<Row v-for="item in page.items" :key="item.code" :item="item"/>
		</div>
	</div>
</template>

<script setup>
	import ResourceIcon from '~/components/ResourceIcon.vue';
	import useState from '~/composables/useState.js';
	import Row from '~/components/Page/Officier/Row.vue';
	import { Head, Link } from '@inertiajs/vue3';
	import { computed } from 'vue';

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

	const state = useState();
	const user = computed(() => state.user);
</script>