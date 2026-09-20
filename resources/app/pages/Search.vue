<template>
	<Head :title="$t('pages.search.title')"/>
	<div class="game-page page-search">
		<UiHeading :title="$t('pages.search.title')" class="game-heading"/>
		<UiPanel class="game-panel">
			<form class="game-form search-form" @submit.prevent="search">
				<label>
					{{ $t('pages.search.type_label') }}
					<select v-model="type">
						<option value="playername">{{ $t('pages.search.type_playername') }}</option>
						<option value="planetname">{{ $t('pages.search.type_planetname') }}</option>
						<option value="allytag">{{ $t('pages.search.type_allytag') }}</option>
						<option value="allyname">{{ $t('pages.search.type_allyname') }}</option>
					</select>
				</label>
				<label>
					{{ $t('pages.search.query_label') }}
					<input type="search" name="search" v-model="query">
				</label>
				<UiButton type="submit" :disabled="form.processing">{{ $t('pages.search.search_button') }}</UiButton>
				<div class="game-errors" v-for="(error, key) in form.errors" :key="key">{{ error }}</div>
			</form>
		</UiPanel>
		<UiPanel v-if="sended" class="game-panel">
			<ListPlayers v-if="type === 'playername' || type === 'planetname'" :items="page.items"/>
			<ListAlliances v-else :items="page.items"/>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiHeading, UiPanel } from '~/components/UI';
	import { ref, watch } from 'vue';
	import ListPlayers from '~/components/Page/Search/ListPlayers.vue';
	import ListAlliances from '~/components/Page/Search/ListAlliances.vue';
	import { Head, useForm } from '@inertiajs/vue3';

	defineProps({
		page: Object,
	});

	const form = useForm({ query: '', type: 'playername' });
	const query = ref('');
	const type = ref('playername');
	const sended = ref(false);

	watch(type, () => {
		sended.value = false;
	});

	function search() {
		if (form.processing) return;

		form.query = query.value;
		form.type = type.value;
		form.post('/search', {
			onSuccess: () => {
				sended.value = true;
			}
		});
	}
</script>