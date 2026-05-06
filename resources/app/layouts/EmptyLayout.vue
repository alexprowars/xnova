<template>
	<div class="application">
		<main>
			<div class="main-content">
				<div class="main-content-row">
					<ErrorMessage v-if="error" :data="error"/>
					<slot/>
				</div>
			</div>
		</main>
	</div>
</template>

<script setup>
	import ErrorMessage from '../components/ErrorMessage.vue'
	import useStore from '~/store';
	import { computed } from 'vue';

	const error = computed(() => {
		return useStore().error || false;
	});
</script>