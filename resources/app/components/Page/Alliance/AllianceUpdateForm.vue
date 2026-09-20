<template>
	<section class="alliance-panel"><h2>{{ $t('pages.alliance.ui.settings') }}</h2>
		<form class="alliance-form" @submit.prevent="save">
			<div class="alliance-fields">
				<label>{{ $t('pages.alliance.index.homepage') }}<input type="text" name="web" v-model="form.web"></label>
				<label>{{ $t('pages.alliance.ui.owner_rank') }}<input type="text" name="owner_rank" v-model="form.owner_rank"></label>
				<label>{{ $t('pages.alliance.index.requests') }}<select name="request_notallow" v-model="form.public"><option :value="0">{{ $t('pages.alliance.ui.closed') }}</option><option :value="1">{{ $t('pages.alliance.ui.open') }}</option></select></label>
				<label>{{ $t('pages.alliance.ui.logo') }}<input type="file" name="image" @change="form.image = $event.target.files[0]"></label>
			</div>
			<div v-if="data.image" class="alliance-logo-preview"><img :src="data.image" :alt="$t('pages.alliance.ui.logo')"><label class="alliance-check"><input type="checkbox" name="delete_image" value="Y" v-model="form.delete_image">{{ $t('pages.alliance.ui.delete_logo') }}</label></div>
			<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
			<div class="alliance-actions"><button type="submit" class="button" :disabled="form.processing">{{ $t('pages.alliance.members.save') }}</button></div>
		</form>
	</section>
</template>

<script setup>
	import { useForm } from '@inertiajs/vue3';

	const props = defineProps({
		data: Object,
	});

	const form = useForm({
		web: props.data['web'],
		image: null,
		delete_image: null,
		owner_rank: props.data['owner_rank'],
		public: props.data['public'] ? 1 : 0,
	});

	function save() {
		if (form.processing) return;

		form.post('/alliance/admin', {
			preserveScroll: true,
			forceFormData: true,
		});
	}
</script>