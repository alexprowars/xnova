<template>
	<div class="block">
		<div class="title">Создать новый ранг</div>
		<div class="content">
			<form class="block-table text-center" @submit.prevent="save">
				<div class="grid grid-cols-2">
					<div class="th">Имя ранга</div>
					<div class="th"><input type="text" v-model="form.name" :class="{error: v$.name.$error}" size="20" maxlength="30"></div>
				</div>
				<div>
					<div class="c"><button type="submit" class="button">Создать</button></div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { useVuelidate } from '@vuelidate/core';
	import { required } from '@vuelidate/validators';
	import { useForm } from '@inertiajs/vue3';

	const form = useForm({
		name: '',
	})

	const validations = {
		name: {
			required
		},
	}

	const v$ = useVuelidate(
		validations,
		form,
		{ $autoDirty: true }
	);

	async function save() {
		if (!await v$.value.$validate()) {
			return
		}

		form.post('/alliance/admin/ranks/create', {
			onSuccess() {
				form.reset();
				v$.value.$reset();
			}
		});
	}
</script>