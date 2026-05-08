<template>
	<form method="post" class="block-table" @submit.prevent="create">
		<div class="grid">
			<div class="th">
				<input :class="{error: v$.name.$error}" type="text" v-model="form.name" size="50">
				<br>
				<button type="submit">{{ $t('pages.fleets.verband.create_submit') }}</button>
			</div>
		</div>
	</form>
</template>

<script setup>
	import { useForm } from '@inertiajs/vue3';
	import { required, minLength, maxLength } from '@vuelidate/validators';
	import { useVuelidate } from '@vuelidate/core';

	const props = defineProps({
		id: Number,
	});

	const form = useForm({
		name: 'AKS' + rand(100000, 999999999),
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

	async function create() {
		if (!await v$.value.$validate()) {
			return;
		}

		form.post('/fleet/verband/' + props['id']);
	}

	function rand (min, max) {
		return Math.floor(Math.random() * (max - min + 1)) + min;
	}
</script>