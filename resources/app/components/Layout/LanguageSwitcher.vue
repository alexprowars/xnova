<template>
	<div class="game-language-switch" role="group" :aria-label="$t('pages.options.language')">
		<button v-for="language in languages" :key="language.code" type="button" class="game-language-option" :class="{ 'is-active': state.locale === language.code }" :aria-label="language.name" :aria-pressed="state.locale === language.code" :title="language.name" :disabled="changingLanguage" @click="changeLanguage(language.code)">
			<component :is="language.flag" aria-hidden="true"/>
		</button>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { router } from '@inertiajs/vue3';
	import { ref } from 'vue';
	import RussianFlagIcon from '~/images/flags/ru.svg?component';
	import BritishFlagIcon from '~/images/flags/gb.svg?component';

	const state = useState();
	const changingLanguage = ref(false);
	const languages = [
		{ code: 'ru', name: 'Русский', flag: RussianFlagIcon },
		{ code: 'en', name: 'English', flag: BritishFlagIcon },
	];

	function changeLanguage(locale) {
		if (changingLanguage.value || state.locale === locale) {
			return;
		}

		changingLanguage.value = true;
		router.post('/locale', { locale }, {
			preserveScroll: true,
			onFinish: () => {
				changingLanguage.value = false;
			},
		});
	}
</script>
