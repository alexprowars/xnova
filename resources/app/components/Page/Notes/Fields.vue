<template>
	<div class="notes-fields">
		<label>
			<span>{{ $t('pages.notes.create.subject') }}</span>
			<input
				type="text"
				name="title"
				maxlength="30"
				v-model="form.title"
				:class="{error: form.errors.title}"
				:placeholder="$t('pages.notes.create.subject_placeholder')"
			>
		</label>
		<label>
			<span>{{ $t('pages.notes.create.priority') }}</span>
			<select v-model="form.priority">
				<option :value="2">{{ $t('pages.notes.create.priority_important') }}</option>
				<option :value="1">{{ $t('pages.notes.create.priority_normal') }}</option>
				<option :value="0">{{ $t('pages.notes.create.priority_unimportant') }}</option>
			</select>
		</label>
	</div>
	<div class="notes-field-label">{{ $t('pages.notes.message') }}</div>
	<TextEditor name="message" v-model="form.message" :class="{error: form.errors.message}"/>
	<div v-if="Object.keys(form.errors).length" class="ui-errors" role="alert">
		<span v-for="(error, field) in form.errors" :key="field">{{ error }}</span>
	</div>
</template>

<script setup>
	import TextEditor from '~/components/TextEditor.vue';
	defineProps({
		form: Object,
	});
</script>