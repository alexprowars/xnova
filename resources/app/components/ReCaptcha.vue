<template>
	<div ref="recaptchaRef"></div>
</template>

<script setup lang="ts">
	import { useTemplateRef } from 'vue';
	import { useI18n } from 'vue-i18n';
	import { useScriptTag } from '@vueuse/core';

	const { action } = defineProps({
		action: {
			type: String,
			default: 'submit',
		}
	});

	const recaptchaRef = useTemplateRef('recaptchaRef');
	const model = defineModel({ default: '' });
	const siteKey = import.meta.env.VITE_RECAPTCHA_KEY;

	useScriptTag(
		'https://www.google.com/recaptcha/enterprise.js?render=explicit&hl=' + (useI18n()?.locale.value || ''),
		() => {
			window.grecaptcha.enterprise.ready(() => {
				renderRecaptcha();
			});
		},
		{
			defer: true,
			async: true,
		}
	);

	function renderRecaptcha() {
		window.grecaptcha.enterprise.render(recaptchaRef.value, {
			sitekey: siteKey,
			action: action,
			callback: (token: string) => {
				model.value = token;
			},
		});
	}
</script>