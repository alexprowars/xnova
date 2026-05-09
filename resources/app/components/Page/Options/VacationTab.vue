<template>
	<form ref="formRef" method="post" @submit.prevent="send">
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
					<button type="submit">{{ $t('pages.options.save') }}</button>
				</div>
			</div>
		</div>
	</form>
</template>

<script setup>
	import { computed, useTemplateRef } from 'vue';
	import { useApiSubmit } from '~/composables/useApi.js';
	import { useSuccessNotification } from '~/composables/useToast.js';
	import { router, usePage } from '@inertiajs/vue3';

	const page = usePage();
	const user = computed(() => page.props.user);

	const formRef = useTemplateRef('formRef');

	function send() {
		useApiSubmit('/options', new FormData(formRef.value), () => {
			useSuccessNotification('Настройки успешно изменены');

			router.reload();
		});
	}
</script>