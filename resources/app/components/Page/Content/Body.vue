<template>
	<article class="game-page page-content"><UiPanel class="game-panel"><UiHeading v-if="title" class="game-heading"><h1 v-html="title"></h1></UiHeading><div class="game-prose content-body" v-html="body"></div></UiPanel></article>
</template>

<script setup>
	import { UiHeading, UiPanel } from '~/components/UI';
	defineProps({ title: String, body: String });
</script>