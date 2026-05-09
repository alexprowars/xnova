<template>
	<Head :title="$t('pages.fleets.shortcut.create.meta_title')"/>
	<div class="block">
		<div class="title">{{ $t('pages.fleets.shortcut.create.heading') }}</div>
		<div class="content">
			<form method="post" class="block-table" @submit.prevent="send">
				<div class="grid">
					<div class="th text-center">
						<input type="text" name="title" v-model.trim="form.name" size="32" maxlength="32" :title="$t('pages.fleets.shortcut.form.title_name')">
						<input type="text" name="galaxy" v-model.number="form.galaxy" size="3" maxlength="2" :title="$t('pages.fleets.shortcut.form.title_galaxy')">
						<input type="text" name="system" v-model.number="form.system" size="3" maxlength="3" :title="$t('pages.fleets.shortcut.form.title_system')">
						<input type="text" name="planet" v-model.number="form.planet" size="3" maxlength="2" :title="$t('pages.fleets.shortcut.form.title_planet')">
						<select name="planet_type" v-model.number="form.planet_type">
							<option v-for="index in Object.keys($tm('planet_type'))" :value="index">{{ $t('planet_type.' + index) }}</option>
						</select>
					</div>
				</div>
				<div class="grid">
					<div class="th text-center">
						<button type="reset" class="button">{{ $t('pages.fleets.shortcut.form.clear') }}</button>
						<button type="submit" class="button">{{ $t('pages.fleets.shortcut.create.submit') }}</button>
					</div>
				</div>
				<div class="grid">
					<div class="c text-center">
						<Link href="/fleet/shortcut">{{ $t('pages.fleets.shortcut.form.back') }}</Link>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { ref } from 'vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';

	const props = defineProps({
		data: Object,
	});

	const form = useForm({
		name: '',
		galaxy: props.data.galaxy,
		system: props.data.system,
		planet: props.data.planet,
		planet_type: props.data.planet_type,
	});

	const { t } = useI18n();

	function send() {
		form.post('/fleet/shortcut', {
			onSuccess() {
				useSuccessNotification(t('pages.fleets.shortcut.create.notify_success'));
			}
		});
	}
</script>