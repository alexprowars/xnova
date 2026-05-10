<template>
	<Head :title="$t('pages.notes.create.page_title')"/>
	<div>
		<div class="block">
			<div class="title">{{ $t('pages.notes.create.title') }}</div>
			<div class="content">
				<form method="post" class="block-table text-center" @submit.prevent="create">
					<div class="grid grid-cols-2">
						<div class="th middle">
							<div>
								{{ $t('pages.notes.create.priority') }}
								<select v-model="form.priority">
									<option value="2">{{ $t('pages.notes.create.priority_important') }}</option>
									<option value="1">{{ $t('pages.notes.create.priority_normal') }}</option>
									<option value="0">{{ $t('pages.notes.create.priority_unimportant') }}</option>
								</select>
							</div>
						</div>
						<div class="th middle">
							<div>
								{{ $t('pages.notes.create.subject') }} <input type="text" name="title" size="30" maxlength="30" v-model="form.title" :placeholder="$t('pages.notes.create.subject_placeholder')">
							</div>
						</div>
					</div>
					<div class="grid">
						<div class="th">
							<TextEditor v-model="form.message"/>
						</div>
					</div>
					<div class="grid">
						<div class="c">
							<button class="button" @click.prevent="reset">{{ $t('pages.notes.create.reset') }}</button>
							<button type="submit" class="button">{{ $t('pages.notes.create.save') }}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
		<div class="mt-2">
			<Link href="/notes" class="button min-w-0">{{ $t('pages.notes.create.back') }}</Link>
		</div>
	</div>
</template>

<script setup>
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import TextEditor from '~/components/TextEditor.vue';

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
		form.post('/notes/create');
	}
</script>
