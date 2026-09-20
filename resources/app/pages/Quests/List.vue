<template>
	<Head :title="$t('pages.quests.title')"/>
	<div class="page-quests-list">
		<header class="quests-heading">
			<h1>{{ $t('pages.quests.current') }}</h1>
			<div class="quests-overall"><span>{{ $t('pages.quests.completed_count', { count: completed, total: page.items.length }) }}</span><div class="quests-progress" role="progressbar" :aria-label="$t('pages.quests.progress')" :aria-valuenow="completed" :aria-valuemax="page.items.length || 1" aria-valuemin="0"><span :style="{ width: (page.items.length ? completed / page.items.length * 100 : 0) + '%' }"></span></div></div>
		</header>
		<div class="quests-list">
			<component :is="quest.available ? Link : 'div'" v-for="quest in page.items" :key="quest.id" :href="quest.available ? '/quests/' + quest.id : undefined" class="quest-row" :class="{ 'is-complete': quest.finish, 'is-available': quest.available, 'is-locked': !quest.available && !quest.finish }">
				<div class="quest-image"><img :src="'/assets/images/quests/' + quest.id + '.jpg'" alt="" width="48" height="48" loading="lazy"><span>{{ quest.id }}</span></div>
				<div class="quest-content">
					<h2>{{ quest.title }}</h2>
					<div v-if="quest.available === false && Object.keys(quest.required).length" class="quest-requirements">
						<span class="quest-requirements-label">{{ $t('pages.quests.requirements') }}</span>
						<template v-for="(req, key) in quest.required" :key="key">
							<span v-if="key === 'quest'" :class="!page.quests[req] || page.quests[req].finish === 0 ? 'is-missing' : 'is-met'">{{ $t('pages.quests.require_quest', { id: req }) }}</span>
							<span v-else-if="key === 'level_minier'" :class="user.lvl.mine.l < req ? 'is-missing' : 'is-met'">{{ $t('pages.quests.require_mine', { level: req }) }}</span>
							<span v-else-if="key === 'level_raid'" :class="user.lvl.raid.l < req ? 'is-missing' : 'is-met'">{{ $t('pages.quests.require_raid', { level: req }) }}</span>
						</template>
					</div>
				</div>
				<span class="quest-status">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path v-if="quest.finish" d="m5 12 4 4L19 6"/><template v-else-if="!quest.available"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></template><path v-else d="M12 3v18m-9-9h18M19 12a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z"/></svg>
					{{ $t(quest.finish ? 'pages.quests.completed' : quest.available ? 'pages.quests.available' : 'pages.quests.locked') }}
				</span>
				<svg v-if="quest.available" class="quest-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7"/></svg>
			</component>
			<div v-if="!page.items.length" class="quests-empty">{{ $t('pages.quests.empty') }}</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import { Head, Link } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineOptions({ layout: { view: { resources: false } } });
	const props = defineProps({ page: Object });
	const state = useState();
	const user = computed(() => state.user);
	const completed = computed(() => props.page.items.filter((quest) => quest.finish).length);
</script>