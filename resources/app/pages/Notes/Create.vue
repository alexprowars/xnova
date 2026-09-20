<template>
	<Head :title="$t('pages.notes.create.page_title')"/>
	<div class="page-notes">
		<UiBackLink href="/notes">{{ $t('pages.notes.back_to_list') }}</UiBackLink>
		<UiHeading :title="$t('pages.notes.create.title')"/>
		<UiPanel clip>
			<form class="ui-form-body" method="post" @submit.prevent="create">
				<NoteFields :form="form"/>
				<div class="ui-actions">
					<UiButton variant="secondary" :disabled="form.processing" @click="reset">{{ $t('pages.notes.create.reset') }}</UiButton>
					<UiButton type="submit" :disabled="form.processing">{{ $t('pages.notes.create.save') }}</UiButton>
				</div>
			</form>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiBackLink, UiButton, UiHeading, UiPanel } from '~/components/UI';
	import { Head, useForm } from '@inertiajs/vue3';
	import NoteFields from '~/components/Page/Notes/Fields.vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const form = useForm({
		priority: 1,
		title: '',
		message: '',
	});

	function reset() {
		form.reset();
	}

	function create() {
		if (form.processing) return;

		form.post('/notes/create');
	}
</script>