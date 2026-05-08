<template>
	<Head :title="$t('pages.fleets.shortcut.edit.meta_title')"/>
	<div class="block">
		<div class="title">{{ page['name'] }} [{{ page['galaxy'] }}:{{ page['system'] }}:{{ page['planet'] }}]</div>
		<div class="content">
			<form method="post" class="block-table" @submit.prevent="update">
				<div class="grid">
					<div class="th">
						<input type="text" name="title" v-model="form.name" size="32" maxlength="32" :title="$t('pages.fleets.shortcut.form.title_name')">
						<input type="text" name="galaxy" v-model.number="form.galaxy" size="3" maxlength="2" :title="$t('pages.fleets.shortcut.form.title_galaxy')">
						<input type="text" name="system" v-model.number="form.system" size="3" maxlength="3" :title="$t('pages.fleets.shortcut.form.title_system')">
						<input type="text" name="planet" v-model.number="form.planet" size="3" maxlength="2" :title="$t('pages.fleets.shortcut.form.title_planet')">
						<select name="planet_type" v-model.number="form.planet_type">
							<option v-for="index in Object.keys($tm('planet_type'))" :value="index">{{ $t('planet_type.' + index) }}</option>
						</select>
					</div>
				</div>
				<div class="grid">
					<div class="th">
						<button type="reset">{{ $t('pages.fleets.shortcut.form.clear') }}</button>
						<button type="submit">{{ $t('pages.fleets.shortcut.edit.update') }}</button>
						<button type="button" @click.prevent="remove">{{ $t('pages.fleets.shortcut.edit.delete') }}</button>
					</div>
				</div>
				<div class="grid">
					<div class="c">
						<Link href="/fleet/shortcut">{{ $t('pages.fleets.shortcut.form.back') }}</Link>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '../../../composables/useToast.js';

	const props = defineProps({
		data: Object,
	});

	const form = useForm({
		name: props.data.name,
		galaxy: props.data.galaxy,
		system: props.data.system,
		planet: props.data.planet,
		planet_type: props.data.planet_type,
	});

	function update() {
		form.post('/fleet/shortcut/' + props.data.id, {
			onSuccess() {
				useSuccessNotification(t('pages.fleets.shortcut.edit.notify_updated'));
			}
		});
	}

	function remove() {
		useForm().delete('/fleet/shortcut/' + props.data.id, {
			onSuccess() {
				useSuccessNotification(t('pages.fleets.shortcut.edit.notify_deleted'));
			}
		});
	}
</script>