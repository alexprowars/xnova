<template>
	<header class="ui-heading">
		<slot>
			<h1>
				<slot name="title">{{ title }}</slot>
				<UiCount v-if="count !== undefined && count !== null">{{ count }}</UiCount>
			</h1>
			<slot name="actions"/>
		</slot>
	</header>
</template>

<script setup>
	import UiCount from './UiCount.vue';

	defineProps({
		title: String,
		count: [Number, String],
	});
</script>
