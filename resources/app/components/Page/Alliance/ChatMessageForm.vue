<template>
	<div class="block">
		<div class="title">Отправить сообщение в чат альянса</div>
		<div class="content">
			<form class="block-table text-center" method="post" @submit.prevent="emit('send')">
				<div class="grid">
					<div class="th">
						<TextEditor v-model="value"/>
					</div>
				</div>
				<div class="grid">
					<div class="c">
						<button class="button" @click.prevent="reset">Очистить</button>
						<button type="submit" class="button">Отправить</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script setup>
	import TextEditor from '~/components/TextEditor.vue';

	const value = defineModel();
	const emit = defineEmits(['send']);

	function reset() {
		value.value = '';
	}
</script>
