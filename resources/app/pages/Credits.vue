<template>
	<Head :title="$t('pages.credits.title')"/>
	<div class="game-page page-credits"><UiHeading class="game-heading"><h1><CreditsIcon aria-hidden="true"/>{{ $t('pages.credits.title') }}</h1></UiHeading>
		<UiPanel class="game-panel credits-intro"><CreditsIcon aria-hidden="true"/><div><p>{{ $t('pages.credits.intro') }}</p><strong>{{ $t('pages.credits.rate') }}</strong></div></UiPanel>
		<UiPanel class="game-panel"><h2>{{ $t('pages.credits.purchase') }}<UiCount class="game-count">ID {{ user.id }}</UiCount></h2><form class="game-form" @submit.prevent="pay">
			<label>{{ $t('pages.credits.recipient') }}<input type="text" inputmode="numeric" name="userId" v-model="form.userId" :placeholder="String(user.id)"><small>{{ $t('pages.credits.recipient_hint') }}</small></label>
			<label>{{ $t('pages.credits.amount') }}<input type="text" inputmode="numeric" name="summ" v-model="form.summ"></label>
			<div v-for="(error, key) in form.errors" :key="key" class="game-errors">{{ error }}</div><div class="game-actions"><UiButton type="submit" :disabled="form.processing">{{ $t('pages.credits.buy') }}</UiButton></div>
		</form></UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiCount, UiHeading, UiPanel } from '~/components/UI';
	import CreditsIcon from '~/images/icons/resources/credits.svg?component';
	import useState from '~/composables/useState.js';
	import { Head, useForm } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const state = useState();
	const user = computed(() => state.user);

	const form = useForm({ userId: '', summ: '10' });

	function pay() {
		if (form.processing) return;

		form.post('/credits/pay', {
			preserveUrl: true,
		});
	}
</script>