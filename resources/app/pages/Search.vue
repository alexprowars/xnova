<template>
	<Head :title="$t('pages.search.title')"/>
	<div class="page-search">
		<div class="block">
			<div class="title">{{ $t('pages.search.title') }}</div>
			<div class="content">
				<div class="block-table text-center">
					<div class="grid">
						<div class="th middle">
							<select v-model="type">
								<option value="playername">{{ $t('pages.search.type_playername') }}</option>
								<option value="planetname">{{ $t('pages.search.type_planetname') }}</option>
								<option value="allytag">{{ $t('pages.search.type_allytag') }}</option>
								<option value="allyname">{{ $t('pages.search.type_allyname') }}</option>
							</select>
							&nbsp;&nbsp;
							<input type="text" name="search" v-model="query">
							&nbsp;&nbsp;
							<button @click.prevent="search">{{ $t('pages.search.search_button') }}</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div v-if="sended" class="mt-2">
			<ListPlayers v-if="type === 'playername' || type === 'planetname'" :items="items"/>
			<ListAlliances v-else :items="items"/>
		</div>
	</div>
</template>

<script setup>
	import { ref, watch } from 'vue';
	import ListPlayers from '../components/Page/Search/ListPlayers.vue';
	import ListAlliances from '../components/Page/Search/ListAlliances.vue';
	import { Head, useForm } from '@inertiajs/vue3';

	defineProps({
		items: {
			type: Array,
			default: () => [],
		}
	})

	const query = ref('');
	const type = ref('playername');
	const sended = ref(false);

	watch(type, () => {
		sended.value = false;
	});

	function search() {
		useForm({
			query: query.value,
			type: type.value,
		})
		.post('/search', {
			onSuccess: () => {
				sended.value = true;
			}
		});
	}
</script>