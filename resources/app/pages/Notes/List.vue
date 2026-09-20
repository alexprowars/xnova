<template>
	<Head :title="$t('pages.notes.index.page_title')"/>
	<div class="page-notes">
		<UiHeading :title="$t('pages.notes.index.title')" :count="page.items.length">
			<template #actions>
				<UiButton :as="Link" href="/notes/create">
					<PlusIcon aria-hidden="true"/>
					{{ $t('pages.notes.index.create_new') }}
				</UiButton>
			</template>
		</UiHeading>
		<div v-if="page.items.length" class="notes-selection">
			<label>
				<input type="checkbox" v-model="selectAll" :indeterminate="deleteItems.length > 0 && !selectAll">
				{{ $t('pages.notes.select_all') }}
			</label>
			<span v-if="deleteItems.length">{{ $t('pages.notes.selected', { count: deleteItems.length }) }}</span>
			<UiButton variant="danger" :disabled="!deleteItems.length || deleteForm.processing" @click="deleteNotes">
				<TrashIcon aria-hidden="true"/>
				{{ $t('pages.notes.index.delete_selected') }}
			</UiButton>
		</div>
		<UiPanel clip>
			<UiEmptyState v-if="!page.items.length">{{ $t('pages.notes.index.no_notes') }}</UiEmptyState>
			<div v-for="item in page.items" :key="item.id" class="note-row" :class="{ 'is-selected': deleteItems.includes(item.id) }">
				<input
					:value="item.id"
					v-model="deleteItems"
					type="checkbox"
					:aria-label="$t('pages.notes.select_note', { title: item.title })"
				>
				<Link :href="'/notes/' + item.id" class="note-link">
					<span class="note-title" :class="'priority-' + priorities[item.color]">{{ item.title }}</span>
					<span v-if="priorities[item.color]" class="note-priority" :class="'priority-' + priorities[item.color]">
						{{ $t('pages.notes.create.priority_' + priorities[item.color]) }}
					</span>
					<time v-if="item.date" :datetime="item.date">{{ $formatDate(item.date, 'DD MMM YYYY HH:mm') }}</time>
					<span class="note-open" aria-hidden="true">›</span>
				</Link>
			</div>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiEmptyState, UiHeading, UiPanel } from '~/components/UI';
	import PlusIcon from '~/images/icons/plus.svg?component';
	import TrashIcon from '~/images/icons/trash.svg?component';
	import { computed, ref, watch } from 'vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { useI18n } from 'vue-i18n';

	const { t } = useI18n();

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

	const deleteItems = ref([]);
	const deleteForm = useForm({ id: [] });
	const selectAll = computed({
		get: () => props.page.items.length > 0 && props.page.items.every(item => deleteItems.value.includes(item.id)),
		set: value => { deleteItems.value = value ? props.page.items.map(item => item.id) : []; },
	});
	const priorities = { red: 'important', yellow: 'normal', lime: 'unimportant' };
	watch(() => props.page.items, () => { deleteItems.value = []; });

	function deleteNotes() {
		if (!deleteItems.value.length || deleteForm.processing) return;

		deleteForm.id = deleteItems.value;
		deleteForm.delete('/notes', {
			preserveUrl: true,
			preserveScroll: true,
			onSuccess() {
				useSuccessNotification(t('pages.notes.index.deleted'));
				deleteItems.value = [];
			}
		});
	}
</script>