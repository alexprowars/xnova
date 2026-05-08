<template>
	<Head :title="$t('pages.notes.edit.page_title')"/>
	<div>
		<div class="block">
			<div class="title">{{ $t('pages.notes.view.title') }}</div>
			<div class="content">
				<div class="block-table">
					<div class="grid">
						<div class="th font-normal">
							<TextViewer :text="form['message']"/>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="block">
			<div class="title">{{ $t('pages.notes.edit.title') }}</div>
			<div class="content">
				<form method="post" class="block-table text-center" @submit.prevent="update">
					<div class="grid grid-cols-2">
						<div class="th middle">
							<div>
								{{ $t('pages.notes.edit.priority') }}
								<select v-model="form['priority']">
									<option value="2">{{ $t('pages.notes.edit.priority_important') }}</option>
									<option value="1">{{ $t('pages.notes.edit.priority_normal') }}</option>
									<option value="0">{{ $t('pages.notes.edit.priority_unimportant') }}</option>
								</select>
							</div>
						</div>
						<div class="th middle">
							<div>
								{{ $t('pages.notes.edit.subject') }} <input type="text" name="title" size="30" maxlength="30" v-model="form['title']" :placeholder="$t('pages.notes.edit.subject_placeholder')">
							</div>
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<TextEditor v-model="form['message']"/>
						</div>
					</div>
					<div class="grid">
						<div class="c">
							<button @click.prevent="reset">{{ $t('pages.notes.edit.reset') }}</button>
							<button type="submit">{{ $t('pages.notes.edit.save') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="mt-2">
			<Link href="/notes" class="button">{{ $t('pages.notes.edit.back') }}</Link>
		</div>
	</div>
</template>

<script setup>
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import TextViewer from '../../components/TextViewer.vue';
	import TextEditor from '../../components/TextEditor.vue';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const props = defineProps({
		item: Object,
	});

	const form = useForm({
		priority: props.item['priority'],
		title: props.item['title'],
		message: props.item['message'],
	});

	function reset() {
		form.reset();
	}

	function update() {
		form.post('/notes/' + props.item['id']);
	}
</script>