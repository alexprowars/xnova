<template>
	<div class="block page-overview-bonus">
		<div class="title text-center">
			{{ $t('pages.overview.daily_bonus.title') }}
		</div>
		<div class="content block-table">
			<div class="grid">
				<div class="th text-center">
					<div v-html="$t('pages.overview.daily_bonus.row_1', [$formatNumber(amount)])"></div>
					<div class="mb-4">{{ $t('pages.overview.daily_bonus.row_2') }}</div>
					<button @click.prevent="getBonus" class="button">
						{{ $t('pages.overview.daily_bonus.button') }}
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { useErrorNotification } from '../../../composables/useToast.js';
	import { router } from '@inertiajs/vue3';
	import { useApiPost } from '../../../composables/useApi.js';

	defineProps({
		amount: Number,
	});

	async function getBonus () {
		try {
			await useApiPost('/user/daily');

			router.reload();
		} catch (e) {
			useErrorNotification(e.message);
		}
	}
</script>