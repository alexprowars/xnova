<template>
	<Head :title="$t('pages.notes.index.page_title')"/>
	<div class="block page-notes">
		<div class="title">
			{{ $t('pages.notes.index.title') }}
		</div>
		<div class="content">
			<div class="block-table">
				<div class="grid grid-cols-12">
					<div class="col-span-1 c"></div>
					<div class="col-span-3 c">{{ $t('pages.notes.index.date') }}</div>
					<div class="col-span-8 c">{{ $t('pages.notes.index.subject') }}</div>
				</div>
				<div class="grid grid-cols-12" v-for="item in page.items">
					<div class="col-span-1 th text-center">
						<input :value="item['id']" v-model="deleteItems" type="checkbox">
					</div>
					<div class="col-span-3 th text-center">
						{{ $formatDate(item['time'], 'DD MMM YYYY HH:mm') }}
					</div>
					<div class="col-span-8 th">
						<Link :href="'/notes/' + item['id']">
							<span :style="'color:'+item['color']">{{ item['title'] }}</span>
						</Link>
					</div>
				</div>
				<div class="grid" v-if="page.items.length === 0">
					<div class="th">{{ $t('pages.notes.index.no_notes') }}</div>
				</div>
			</div>
		</div>
	</div>
	<div class="mt-2">
		<button v-if="deleteItems.length > 0" class="button negative" @click="deleteNotes">{{ $t('pages.notes.index.delete_selected') }}</button>
		<Link class="button" href="/notes/create">{{ $t('pages.notes.index.create_new') }}</Link>
	</div>
</template>

<script setup>
	import { ref } from 'vue';
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

	function deleteNotes() {
		useForm({ id: deleteItems.value }).delete('/notes', {
			preserveUrl: true,
			preserveScroll: true,
			onSuccess() {
				useSuccessNotification(t('pages.notes.index.deleted'));
				deleteItems.value = [];
			}
		});
	}
</script>