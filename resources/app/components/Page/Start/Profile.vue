<template>
	<UiPanel class="game-panel">
		<header class="start-heading">
			<span class="game-eyebrow">01 / 03</span>
			<h2>{{ $t('pages.start.main_info') }}</h2>
		</header>
		<form class="game-form" @submit.prevent="next">
			<label>
				{{ $t('pages.start.game_nickname') }}
				<input :class="{ error: v$.name.$error || form.errors.name }" name="name" maxlength="30" type="text" v-model.trim="form.name" @input="form.clearErrors('name')">
			</label>
			<div v-if="v$.name.$error" class="game-errors">
				{{ $t('pages.auth.check_fields') }}
			</div>
			<label>
				{{ $t('pages.start.interface_language') }}
				<select name="locale" v-model="form.locale" @change="locale = form.locale; form.clearErrors('locale')">
					<option value="ru">Русский</option>
					<option value="en">English</option>
				</select>
			</label>
			<div v-if="form.errors.name" class="game-errors">{{ form.errors.name }}</div>
			<div v-if="form.errors.locale" class="game-errors">{{ form.errors.locale }}</div>
			<div class="game-actions">
				<UiButton type="submit" :disabled="!form.name || form.processing">{{ $t('pages.start.continue') }} →</UiButton>
			</div>
		</form>
	</UiPanel>
</template>

<script setup>
	import { useI18n } from 'vue-i18n';
	import { UiButton, UiPanel } from '~/components/UI';
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';

	const { locale } = useI18n();
	const props = defineProps({
		form: { type: Object, required: true },
	});
	const emit = defineEmits(['next']);
	const v$ = useVuelidate({ name: { required } }, props.form, { $autoDirty: true });

	async function next() {
		if (props.form.processing || !await v$.value.$validate()) return;

		emit('next');
	}
</script>