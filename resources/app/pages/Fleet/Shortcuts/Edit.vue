<template>
	<Head :title="$t('pages.fleets.shortcut.edit.meta_title')"/>
	<div class="fleet-page page-fleet-shortcut-form">
		<div class="block">
			<div class="title">{{ page['name'] }} [{{ page['galaxy'] }}:{{ page['system'] }}:{{ page['planet'] }}]</div>
			<div class="content">
				<form method="post" class="fleet-shortcut-form" @submit.prevent="update">
					<div class="fleet-shortcut-fields">
						<label class="fleet-field fleet-shortcut-name">
							<span>{{ $t('pages.fleets.shortcut.form.title_name') }}</span>
							<input type="text" name="title" v-model="form.name" maxlength="32">
						</label>
						<label class="fleet-field">
							<span>{{ $t('pages.fleets.shortcut.form.title_galaxy') }}</span>
							<input type="text" name="galaxy" v-model.number="form.galaxy" maxlength="2" inputmode="numeric">
						</label>
						<label class="fleet-field">
							<span>{{ $t('pages.fleets.shortcut.form.title_system') }}</span>
							<input type="text" name="system" v-model.number="form.system" maxlength="3" inputmode="numeric">
						</label>
						<label class="fleet-field">
							<span>{{ $t('pages.fleets.shortcut.form.title_planet') }}</span>
							<input type="text" name="planet" v-model.number="form.planet" maxlength="2" inputmode="numeric">
						</label>
						<label class="fleet-field fleet-shortcut-type">
							<span>{{ $t('pages.fleets.checkout.target_type') }}</span>
							<select name="planet_type" v-model.number="form.planet_type">
								<option v-for="index in Object.keys($tm('planet_type'))" :key="index" :value="index">
									{{ $t('planet_type.' + index) }}
								</option>
							</select>
						</label>
					</div>
					<div class="grid">
						<div class="fleet-actions">
							<button type="reset" class="button">{{ $t('pages.fleets.shortcut.form.clear') }}</button>
							<button type="submit" class="button">{{ $t('pages.fleets.shortcut.edit.update') }}</button>
							<button type="button" class="button is-danger" @click.prevent="remove">
								{{ $t('pages.fleets.shortcut.edit.delete') }}
							</button>
						</div>
					</div>
					<div class="grid">
						<div class="fleet-actions">
							<Link href="/fleet/shortcut">{{ $t('pages.fleets.shortcut.form.back') }}</Link>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</template>

<script setup>
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';

	const props = defineProps({
		page: Object,
	});

	const form = useForm({
		name: props.page.name,
		galaxy: props.page.galaxy,
		system: props.page.system,
		planet: props.page.planet,
		planet_type: props.page.planet_type,
	});

	const { t } = useI18n();

	function update() {
		form.post('/fleet/shortcut/' + props.page.id, {
			onSuccess() {
				useSuccessNotification(t('pages.fleets.shortcut.edit.notify_updated'));
			}
		});
	}

	function remove() {
		useForm().delete('/fleet/shortcut/' + props.page.id, {
			onSuccess() {
				useSuccessNotification(t('pages.fleets.shortcut.edit.notify_deleted'));
			}
		});
	}
</script>