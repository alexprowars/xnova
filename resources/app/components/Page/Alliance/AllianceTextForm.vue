<template>
	<section class="alliance-panel">
		<h2>{{ $t('pages.alliance.ui.edit_text') }}</h2>
		<UiTabNavigation :items="tabs" :active="data.text_type" :label="$t('pages.alliance.ui.edit_text')" embedded/>
		<form class="alliance-form" @submit.prevent="save">
			<TextEditor v-model="form.text"/>
			<div v-for="(error, key) in form.errors" :key="key" class="alliance-errors">{{ error }}</div>
			<div class="alliance-actions"><button type="button" class="button is-secondary" :disabled="form.processing" @click="form.text = ''">{{ $t('pages.alliance.ui.clear') }}</button><button type="submit" class="button" :disabled="form.processing">{{ $t('pages.alliance.members.save') }}</button></div>
		</form>
	</section>
</template>

<script setup>
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { UiTabNavigation } from '~/components/UI';
	import { useForm } from '@inertiajs/vue3';
	import TextEditor from '~/components/TextEditor.vue';

	const props = defineProps({
		data: Object,
	});

	const { t } = useI18n();
	const tabs = computed(() => [1, 2, 3].map(id => ({
		id, href: '/alliance/admin?type=' + id, label: t('pages.alliance.ui.text_type_' + id),
	})));

	const form = useForm({
		type: props.data['text_type'],
		text: props.data.text,
	});

	function save() {
		if (form.processing) return;

		form.post('/alliance/admin/text', {
			preserveScroll: true,
		});
	}
</script>