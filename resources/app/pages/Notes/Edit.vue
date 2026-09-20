<template>
	<Head :title="$t('pages.notes.edit.page_title')"/>
	<div class="page-notes">
		<UiBackLink href="/notes">{{ $t('pages.notes.back_to_list') }}</UiBackLink>
		<UiHeading :title="$t('pages.notes.edit.title')"/>
		<UiPanel clip :title="$t('pages.notes.view.title')"><div class="notes-preview-body"><TextViewer :text="form.message"/></div></UiPanel>
		<UiPanel clip><form class="ui-form-body" method="post" @submit.prevent="update">
			<NoteFields :form="form"/>
			<div class="ui-actions"><UiButton variant="secondary" :disabled="form.processing" @click="reset">{{ $t('pages.notes.edit.reset') }}</UiButton><UiButton type="submit" :disabled="form.processing">{{ $t('pages.notes.edit.save') }}</UiButton></div>
		</form></UiPanel>
	</div>
</template>

<script setup>
	import { UiBackLink, UiButton, UiHeading, UiPanel } from '~/components/UI';
	import { Head, useForm } from '@inertiajs/vue3';
	import TextViewer from '~/components/TextViewer.vue';
	import NoteFields from '~/components/Page/Notes/Fields.vue';

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
		priority: props.page.item['priority'],
		title: props.page.item['title'],
		message: props.page.item['message'],
	});

	function reset() {
		form.reset();
	}

	function update() {
		if (form.processing) return;

		form.post('/notes/' + props.page.item['id']);
	}
</script>