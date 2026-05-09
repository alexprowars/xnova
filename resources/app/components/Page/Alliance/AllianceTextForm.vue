<template>
	<div class="block">
		<div class="title">Редактировать текст</div>
		<div class="content">
			<form class="block-table text-center" @submit.prevent="save">
				<div class="grid grid-cols-3">
					<div class="th"><Link href="/alliance/admin?type=1">Внешний текст</Link></div>
					<div class="th"><Link href="/alliance/admin?type=2">Внутренний текст</Link></div>
					<div class="th"><Link href="/alliance/admin?type=3">Текст заявки</Link></div>
				</div>
				<div class="grid">
					<div v-if="data['text_type'] === 3" class="c">Текст заявок альянса</div>
					<div v-else-if="data['text_type'] === 2" class="c">Внутренний текст альянса</div>
					<div v-else class="c">Текст альянса</div>
				</div>
				<div class="grid">
					<div class="th">
						<TextEditor v-model="form.text"/>
					</div>
				</div>
				<div class="grid">
					<div class="th">
						<button type="reset">Очистить</button>
						<button type="submit">Сохранить</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { Link, useForm } from '@inertiajs/vue3';
	import TextEditor from '~/components/TextEditor.vue';

	const props = defineProps({
		data: Object,
	});

	const form = useForm({
		type: props.data['text_type'],
		text: props.data.text,
	});

	function save() {
		form.post('/alliance/admin/text', {
			preserveScroll: true,
		});
	}
</script>