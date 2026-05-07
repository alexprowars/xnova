<template>
	<div class="application">
		<div class="error">
			<div>
				<div v-if="status === 404" class="e404">Вы попали на несуществующую страницу!</div>
				<div>{{ message }}</div>
			</div>
			<div>
				<Game/>
			</div>
		</div>
	</div>
</template>

<script setup>
	import Game from '../components/Layout/Game.vue';
	import App from '../App.vue';
	import EmptyLayout from '../layouts/EmptyLayout.vue';

	defineOptions({
		layout: [App, EmptyLayout],
	});

	defineProps({
		status: Number,
		message: String,
	});
</script>