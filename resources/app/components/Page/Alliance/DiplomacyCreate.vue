<template>
	<div class="block">
		<div class="title">Добавить альянс в список</div>
		<div class="content">
			<form class="block-table text-center" @submit.prevent="save">
				<div class="grid grid-cols-2">
					<div class="th">
						<select v-model="form.alliance">
							<option :value="0">список альянсов</option>
							<option v-for="item in items" :value="item['id']">{{ item['name'] }} [{{ item['tag'] }}]</option>
						</select>
					</div>
					<div class="th">
						<select v-model="form.status">
							<option :value="1">Перемирие</option>
							<option :value="2">Мир</option>
							<option :value="3">Война</option>
						</select>
					</div>
				</div>
				<div>
					<div class="c middle">
						<button type="submit">Добавить</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import { useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '~/composables/useToast.js';

	defineProps({
		items: Array,
	});

	const form = useForm({
		alliance: null,
		status: 1,
	});

	function save() {
		form.post('/alliance/diplomacy/create', {
			onSuccess() {
				useSuccessNotification('Отношение между вашими альянсами успешно добавлено');

				form.reset();
			}
		});
	}
</script>