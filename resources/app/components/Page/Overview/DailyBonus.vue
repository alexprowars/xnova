<template>
	<div class="block page-overview-bonus">
		<div class="title text-center">
			{{ $t('pages.overview.daily_bonus.title') }}
		</div>
		<div class="content text-center">
			<div v-html="$t('pages.overview.daily_bonus.row_1', [$formatNumber(amount)])"></div>
			<div>{{ $t('pages.overview.daily_bonus.row_2') }}</div>
			<button @click.prevent="getBonus" class="button mt-2">
				{{ $t('pages.overview.daily_bonus.button') }}
			</button>
		</div>
	</div>
</template>

<script setup>
	import { useForm } from '@inertiajs/vue3';

	defineProps({
		amount: Number,
	});

	async function getBonus () {
		useForm().post('/user/daily', {
			preserveScroll: true,
		});
	}
</script>