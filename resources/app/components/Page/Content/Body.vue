<template>
	<article class="game-page page-content">
		<UiHeading v-if="title" class="game-heading">
			<h1 v-html="title"></h1>
		</UiHeading>
		<UiPanel class="game-panel">
			<div class="game-prose content-body" v-html="body"></div>
		</UiPanel>
	</article>
</template>

<script setup>
	import { UiHeading, UiPanel } from '~/components/UI';
	defineProps({
		title: String,
		body: String,
	});
</script>