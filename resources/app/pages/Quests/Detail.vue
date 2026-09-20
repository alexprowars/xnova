<template>
	<Head :title="$t('pages.quests.detail_title', { title: page.title })"/>
	<div class="page-quest-detail">
		<Link href="/quests" class="quest-back"><span aria-hidden="true">←</span> {{ $t('pages.quests.back') }}</Link>
		<header class="quest-detail-heading">
			<img :src="'/assets/images/quests/' + page.id + '.jpg'" alt="" width="100" height="100">
			<div class="quest-intro"><span class="quest-number">{{ $t('pages.quests.number', { id: page.id }) }}</span><h1>{{ page.title }}</h1><div class="quest-description" v-html="page.description"></div></div>
		</header>
		<div class="quest-detail-grid">
			<section class="quest-panel quest-objectives">
				<header class="quest-panel-heading"><h2>{{ $t('pages.quests.tasks') }}</h2><span>{{ completedTasks }} / {{ page.task.length }}</span></header>
				<div v-if="page.task.length" class="quest-task-progress" role="progressbar" :aria-label="$t('pages.quests.task_progress')" :aria-valuenow="completedTasks" :aria-valuemax="page.task.length" aria-valuemin="0"><span :style="{ width: completedTasks / page.task.length * 100 + '%' }"></span></div>
				<ul class="quest-task-list">
					<li v-for="(task, index) in page.task" :key="index" :class="{ 'is-done': task[1] }">
						<span class="quest-task-status" :title="$t(task[1] ? 'pages.quests.task_done' : 'pages.quests.task_pending')" role="img" :aria-label="$t(task[1] ? 'pages.quests.task_done' : 'pages.quests.task_pending')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path v-if="task[1]" d="m5 12 4 4L19 6"/><circle v-else cx="12" cy="12" r="6"/></svg></span>
						<span v-html="task[0]"></span>
					</li>
				</ul>
			</section>
			<aside class="quest-panel quest-reward">
				<header class="quest-panel-heading"><h2><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3h8v6a4 4 0 0 1-8 0V3Zm0 2H4v3a4 4 0 0 0 4 4m8-7h4v3a4 4 0 0 1-4 4m-4 1v6m-4 2h8"/></svg>{{ $t('pages.quests.reward') }}</h2></header>
				<div class="quest-reward-content" v-html="page.rewd"></div>
				<div v-if="Object.keys(form.errors).length" class="quest-errors" role="alert"><div v-for="(error, key) in form.errors" :key="key">{{ error }}</div></div>
				<div v-if="!page.errors" class="quest-finish"><button type="button" class="button" :disabled="form.processing" @click.prevent="finish">{{ $t('pages.quests.finish') }}</button></div>
			</aside>
		</div>
		<section v-if="page.solution" class="quest-panel quest-solution">
			<header class="quest-panel-heading"><h2>{{ $t('pages.quests.solution') }}</h2></header>
			<div class="quest-solution-content" v-html="page.solution"></div>
		</section>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { Head, Link, useForm } from '@inertiajs/vue3';
	import { useI18n } from 'vue-i18n';
	import { useSuccessNotification } from '~/composables/useToast.js';

	defineOptions({ layout: { view: { resources: false } } });
	const props = defineProps({ page: Object });
	const { t } = useI18n();
	const form = useForm({});
	const completedTasks = computed(() => props.page.task.filter((task) => task[1]).length);

	function finish() {
		form.post('/quests/' + props.page.id, {
			preserveUrl: true,
			onSuccess() {
				useSuccessNotification(t('pages.quests.finished'));
			}
		});
	}
</script>