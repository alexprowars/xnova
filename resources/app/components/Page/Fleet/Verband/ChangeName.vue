<template>
	<form method="post" @submit.prevent="update">
		<input :class="{error: v$.name.$error}" type="text" name="name" v-model="form.name" size="50">
		<br>
		<button type="submit" class="button">{{ $t('pages.fleets.verband.change_name_submit') }}</button>
	</form>
</template>

<script setup>
	import { useForm } from '@inertiajs/vue3';
	import { maxLength, minLength, required } from '@vuelidate/validators';
	import { useVuelidate } from '@vuelidate/core';

	const props = defineProps({
		id: Number,
		name: String,
	});

	const form = useForm({
		name: props.name,
	});

	const validations = {
		name: {
			required,
			minLength: minLength(5),
			maxLength: maxLength(20),
		}
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function update() {
		if (!await v$.value.$validate()) {
			return;
		}

		form.post('/fleet/verband/' + props['id'] + '/name');
	}
</script>