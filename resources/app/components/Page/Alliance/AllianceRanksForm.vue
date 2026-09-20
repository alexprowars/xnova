<template>
	<form class="alliance-ranks" @submit.prevent="save">
		<section v-for="rank in items" :key="rank.id" class="alliance-panel alliance-rank-card">
			<header><h2>{{ rank.name }}</h2><button type="button" class="button is-danger icon-button" :disabled="form.processing" :title="$t('pages.alliance.ui.delete_rank')" :aria-label="$t('pages.alliance.ui.delete_rank') + ': ' + rank.name" @click="remove(rank.id)"><TrashIcon aria-hidden="true"/></button></header>
			<div class="alliance-permissions"><label v-for="right in rights" :key="right" :class="{ 'is-readonly': !owner && ['delete', 'kick'].includes(right) }"><input type="checkbox" v-model="rank.rights[right]" :disabled="form.processing || (!owner && ['delete', 'kick'].includes(right))"><span>{{ $t('pages.alliance.ui.right_' + right) }}</span></label></div>
		</section>
		<div v-if="!items.length" class="alliance-panel alliance-empty">{{ $t('pages.alliance.ui.no_ranks') }}</div>
		<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
		<div v-if="items.length" class="alliance-actions"><button type="submit" class="button" :disabled="form.processing">{{ $t('pages.alliance.members.save') }}</button></div>
	</form>
</template>

<script setup>
	import TrashIcon from '~/images/icons/trash.svg?component';
	import { useForm } from '@inertiajs/vue3';

	const props = defineProps({
		owner: Boolean,
		items: Array,
	});

	const rights = ['delete', 'kick', 'request', 'memberlist', 'accept', 'admin', 'onlinestatus', 'chat', 'rights', 'diplomacy'];
	const form = useForm({ rigths: {} });

	function save() {
		if (form.processing) return;

		let data = {
			rigths: {}
		};

		for (let i in props.items) {
			let rank = props.items[i];

			data.rigths[rank['id']] = Object.fromEntries(Object.entries(rank['rights']).filter(([key, value]) => value === true));
		}

		form.rigths = data.rigths;
		form.post('/alliance/admin/ranks');
	}

	function remove(id) {
		form.delete('/alliance/admin/ranks/' + id);
	}
</script>