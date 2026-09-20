<template>
	<component :is="as" class="ui-panel" :class="{ 'ui-panel--clip': clip }">
		<slot name="header">
			<h2 v-if="title">{{ title }}</h2>
		</slot>
		<slot/>
	</component>
</template>

<script setup>
	defineProps({
		as: {
			type: String,
			default: 'section',
		},
		title: String,
		clip: Boolean,
	});
</script>
