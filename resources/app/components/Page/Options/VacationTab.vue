<template>
	<Form action="/options" method="post" class="options-card" :on-success="() => useSuccessNotification(t('pages.options.saved'))" v-slot="{ errors, processing }">
		<div v-if="Object.keys(errors).length" class="options-errors" role="alert">
			<div v-for="(error, key) in errors" :key="key">{{ error }}</div>
		</div>
		<div class="options-vacation-status">
			<span>{{ $t('pages.options.vacation_mode_until') }}</span>
			<time :datetime="user.vacation">{{ $formatDate(user.vacation, 'DD MMM YYYY HH:mm:ss') }}</time>
		</div>
		<div class="options-row">
			<div class="options-label">{{ $t('pages.options.nickname') }}</div>
			<div class="options-control">
				<span class="options-value">{{ user.name }}</span>
			</div>
		</div>
		<div class="options-row options-switch-row">
			<label for="options-vacation" class="options-label">
				{{ $t('pages.options.vacation_on') }}
				<span class="options-hint">{{ $t('pages.options.vacation_tip') }}</span>
			</label>
			<div class="options-control">
				<input name="vacation" value="0" type="hidden">
				<input id="options-vacation" name="vacation" value="1" :checked="user.vacation !== null" type="checkbox" class="options-switch">
			</div>
		</div>
		<div class="options-row options-switch-row">
			<label for="options-delete" class="options-label">
				{{ $t('pages.options.delete_on') }}
				<span class="options-hint">{{ $t('pages.options.delete_tip') }}</span>
			</label>
			<div class="options-control">
				<input name="delete" value="0" type="hidden">
				<input id="options-delete" name="delete" value="1" :checked="user.deleted_at !== null" type="checkbox" class="options-switch">
			</div>
		</div>
		<div class="options-row">
			<label for="options-locale" class="options-label">{{ $t('pages.options.language') }}</label>
			<div class="options-control">
				<select id="options-locale" name="locale" v-model="user.locale">
					<option value="en">English</option>
					<option value="ru">Русский</option>
				</select>
			</div>
		</div>
		<div class="options-actions">
			<button type="submit" class="button" :disabled="processing">{{ $t('pages.options.save') }}</button>
		</div>
	</Form>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { Form } from '@inertiajs/vue3';

	const { t } = useI18n();
	const state = useState();
	const user = computed(() => state.user);
</script>