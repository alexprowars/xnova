<template>
	<Head :title="$t('pages.alliance.chat.meta_title')"/>
	<div class="page-alliance-chat">
		<div class="block-table">
			<div class="c text-center">
				<a href="" @click.prevent="router.reload()">{{ $t('pages.alliance.chat.link_refresh') }}</a>
			</div>
			<div v-for="item in items" class="grid grid-cols-12">
				<div class="col-span-2 b text-center middle">
					<div>
						{{ $formatDate(item['time'], 'HH:mm:ss') }}
						<br>
						<a :href="'/players/' + item['user_id']" target="_blank">{{ item['user'] }}</a>
						<a @click.prevent="quote(item)"> -> </a>
					</div>
				</div>
				<div class="col-span-9 b">
					<TextViewer v-if="user['options']['bb_parser']" :text="item['message']"/>
					<div v-else>{{ item['message'] }}</div>
				</div>
				<div v-if="owner" class="col-span-1 b text-center middle">
					<input type="checkbox" :value="item['id']" v-model="marked">
				</div>
			</div>

			<div v-if="items.length === 0" class="grid">
				<div class="b text-center">{{ $t('pages.alliance.chat.empty_messages') }}</div>
			</div>

			<div>
				<div class="th">
					<Pagination :options="pagination"/>
				</div>
			</div>

			<div v-if="marked.length" class="grid">
				<div class="th">
					<select v-model="deleteType">
						<option value="marked">{{ $t('pages.alliance.chat.delete_option_marked') }}</option>
						<option value="unmarked">{{ $t('pages.alliance.chat.delete_option_unmarked') }}</option>
						<option value="all">{{ $t('pages.alliance.chat.delete_option_all') }}</option>
					</select>
					<button @click.prevent="remove">{{ $t('pages.alliance.chat.button_delete') }}</button>
				</div>
			</div>
		</div>

		<ChatMessageForm v-model="form.message" @send="send"/>

		<div class="mt-2">
			<Link href="/alliance" class="button">{{ $t('pages.alliance.chat.link_back_alliance') }}</Link>
		</div>
	</div>
</template>

<script setup>
	import ChatMessageForm from '../../components/Page/Alliance/ChatMessageForm.vue';
	import { computed, ref } from 'vue';
	import TextViewer from '../../components/TextViewer.vue';
	import Pagination from '../../components/Pagination.vue';
	import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
	import { useSuccessNotification } from '../../composables/useToast.js';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		items: Array,
		pagination: Object,
		owner: Boolean,
	});

	const form = useForm({
		message: '',
	});

	const deleteType = ref('marked');
	const marked = ref([]);

	const page = usePage();
	const user = computed(() => page.props.user);

	function quote (messageItem) {
		let text = messageItem['message'] || '';
		text = text.replace(/<br>/gi, "\n");
	    text = text.replace(/<br \/>/gi, "\n");

		form.message = form.message + '[quote author=' + messageItem['user'] + ']' + text + '[/quote]';
	}

	function send() {
		form.post('/alliance/chat', {
			onSuccess() {
				useSuccessNotification('Сообщение отправлено');

				form.reset();
			}
		});
	}

	function remove() {
		useForm({
			type: deleteType.value,
			id: marked.value,
		}).delete('/alliance/chat');
	}
</script>