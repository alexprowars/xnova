<template>
	<form @click.prevent>
		<div class="separator"></div>
		<div class="block-table">
			<div class="grid">
				<div class="th">Заявка от {{ request['name'] }}</div>
			</div>
			<div class="grid">
				<div class="th">{{ request['message'] }}</div>
			</div>
			<div class="grid">
				<div class="c">Форма ответа:</div>
			</div>
			<div class="grid">
				<div class="th"><button @click.prevent="accept">Принять</button></div>
			</div>
			<div class="grid">
				<div class="th">
					<textarea v-model="form.message" cols="40" rows="10"></textarea>
				</div>
			</div>
			<div class="grid">
				<div class="th"><button @click.prevent="reject">Отклонить</button></div>
			</div>
		</div>
		<div class="separator"></div>
	</form>
</template>

<script setup>
	import { useForm } from '@inertiajs/vue3';
	import { useSuccessNotification } from '../../../composables/useToast.js';

	const props = defineProps({
		request: Object,
	});

	const form = useForm({
		id: props.request['id'],
		message: '',
	});

	const emit = defineEmits(['close']);

	function accept() {
		form.post('/alliance/admin/requests/accept', {
			onSuccess() {
				useSuccessNotification('Игрок принят в альянс');
			}
		});
	}

	function reject() {
		form.post('/alliance/admin/requests/reject', {
			onSuccess() {
				useSuccessNotification('Вы отклонили заявку');

				emit('close');
			}
		});
	}
</script>