<template>
	<Modal>
		<Head title="Восстановление пароля"/>
		<RemindForm/>
	</Modal>
</template>

<script setup>
	import { Head } from '@inertiajs/vue3';
	import RemindForm from '~/components/Page/Index/RemindForm.vue';
	import { Modal } from '@inertiaui/modal-vue';
</script>