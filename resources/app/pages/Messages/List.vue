<template>
	<Head :title="$t('pages.messages.index.page_title')"/>
	<section class="page-messages">
		<header class="messages-heading">
			<h1><MessageIcon name="mail"/>{{ $t('pages.messages.index.title') }}<span class="messages-total">{{ page.pagination.total }}</span></h1>
			<div class="messages-filters">
				<label><span>{{ $t('pages.messages.index.category') }}</span><select name="category" v-model="category">
					<option v-for="type in Object.keys($tm('message_types'))" :key="type" :value="type">{{ $t('message_types.' + type) }}</option>
				</select></label>
				<label><span>{{ $t('pages.messages.index.per_page') }}</span><select name="limit" v-model="limit">
					<option v-for="i in limitItems" :key="i" :value="i">{{ i }}</option>
				</select></label>
			</div>
		</header>
		<div v-if="messages.length && canDelete" class="messages-selection">
			<label><input type="checkbox" v-model="checkAll" :indeterminate="deleteItems.length > 0 && !checkAll">{{ $t('pages.messages.index.select_all') }}</label>
			<span v-if="deleteItems.length" class="messages-selected">{{ $t('pages.messages.index.selected', { count: deleteItems.length }) }}</span>
			<button type="button" class="button messages-delete" :disabled="!deleteItems.length" @click="deleteMessages"><MessageIcon name="trash"/>{{ $t('pages.messages.index.delete_selected') }}</button>
		</div>
		<div class="messages-list">
			<MessagesRow v-for="item in messages" :key="item.id" :item="item" :can-delete="canDelete" v-model:delete="deleteItems"/>
		</div>
		<div v-if="!messages.length" class="messages-empty"><MessageIcon name="mail"/><span>{{ $t('pages.messages.index.no_messages') }}</span></div>
		<div v-if="page.pagination.total > page.pagination.limit" class="messages-pagination"><Pagination :options="page.pagination"/></div>
	</section>
</template>

<script setup>
	import MessageIcon from '~/components/Page/Messages/MessageIcon.vue';
	import MessagesRow from '~/components/Page/Messages/Row.vue';
	import { computed, ref, watch } from 'vue';
	import { Head, router, useForm, usePage } from '@inertiajs/vue3';
	import Pagination from '~/components/Pagination.vue';

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

	const category = ref(props.page.category);
	const limit = ref(props.page.limit);
	const canDelete = computed(() => props.page.category !== 101);

	const limitItems = ref([5, 10, 25, 50, 100, 200]);

	const messages = computed(() => props.page.items || []);
	const deleteItems = ref([]);
	const checkAll = computed({
		get: () => messages.value.length > 0 && messages.value.every(item => deleteItems.value.includes(item.id)),
		set: value => { deleteItems.value = value && canDelete.value ? messages.value.map(item => item.id) : []; },
	});

	watch(messages, () => { deleteItems.value = []; });

	watch([category, limit], () => {
		deleteItems.value = [];
		router.get(usePage().url, { category: category.value, limit: limit.value });
	});

	function deleteMessages() {
		if (!canDelete.value) {
			return;
		}

		useForm({ id: deleteItems.value }).delete('/messages/delete', {
			preserveUrl: true,
			preserveScroll: true,
			onSuccess() {
				deleteItems.value = [];
			}
		});
	}
</script>