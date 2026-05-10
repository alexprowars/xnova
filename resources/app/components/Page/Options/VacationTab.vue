<template>
	<Form action="/options" method="post" :on-success="() => useSuccessNotification('Настройки успешно изменены')">
		<div class="block-table text-center">
			<div class="grid">
				<div class="c">{{ $t('pages.options.vacation_mode') }}</div>
			</div>
			<div class="grid">
				<div class="c">{{ $t('pages.options.vacation_mode_until') }}: {{ $formatDate(user.vacation, 'DD MMM YYYY HH:mm:ss') }}</div>
			</div>
			<div class="grid grid-cols-2">
				<div class="th">{{ $t('pages.options.nickname') }}</div>
				<div class="th">{{ user.name }}</div>
			</div>
			<div class="grid grid-cols-2">
				<div class="th"><a :title="$t('pages.options.vacation_tip')">{{ $t('pages.options.vacation_on') }}</a></div>
				<div class="th">
					<input name="vacation" value="0" type="hidden">
					<input name="vacation" value="1" :checked="user.vacation !== null" type="checkbox">
				</div>
			</div>
			<div class="grid grid-cols-2">
				<div class="th"><a :title="$t('pages.options.delete_tip')">{{ $t('pages.options.delete_on') }}</a></div>
				<div class="th">
					<input name="delete" value="0" type="hidden">
					<input name="delete" value="1" :checked="user.deleted_at !== null" type="checkbox">
				</div>
			</div>
			<div class="grid grid-cols-2">
				<div class="th middle">Язык</div>
				<div class="th middle">
					<select name="locale" v-model="user.locale">
						<option value="en">English</option>
						<option value="ru">Русский</option>
					</select>
				</div>
			</div>
			<div class="grid">
				<div class="th">
					<button type="submit" class="button">{{ $t('pages.options.save') }}</button>
				</div>
			</div>
		</div>
	</Form>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { computed } from 'vue';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { Form } from '@inertiajs/vue3';

	const state = useState();
	const user = computed(() => state.user);
</script>