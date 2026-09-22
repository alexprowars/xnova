<template>
	<Head :title="$t('pages.quests.title')"/>
	<div class="page-quests-list">
		<header class="quests-heading">
			<h1>{{ $t('pages.quests.current') }}</h1>
			<div class="quests-overall">
				<span>{{ $t('pages.quests.completed_count', { count: completed, total: page.items.length }) }}</span>
				<div
					class="quests-progress"
					role="progressbar"
					:aria-label="$t('pages.quests.progress')"
					:aria-valuenow="completed"
					:aria-valuemax="page.items.length || 1"
					aria-valuemin="0"
				>
					<span :style="{ width: (page.items.length ? completed / page.items.length * 100 : 0) + '%' }"></span>
				</div>
			</div>
		</header>
		<div class="quests-list">
			<component
				:is="quest.available ? Link : 'div'"
				v-for="quest in page.items"
				:key="quest.id"
				:href="quest.available ? '/quests/' + quest.id : undefined"
				class="quest-row"
				:class="{ 'is-complete': quest.finish, 'is-available': quest.available, 'is-locked': !quest.available && !quest.finish }"
			>
				<div class="quest-image">
					<img :src="'/assets/images/quests/' + quest.id + '.jpg'" alt="" width="48" height="48" loading="lazy">
					<span>{{ quest.id }}</span>
				</div>
				<div class="quest-content">
					<h2>{{ quest.title }}</h2>
					<div v-if="quest.available === false && Object.keys(quest.required).length" class="quest-requirements">
						<span class="quest-requirements-label">{{ $t('pages.quests.requirements') }}</span>
						<template v-for="(req, key) in quest.required" :key="key">
							<span
								v-if="key === 'quest'"
								:class="!page.quests[req] || page.quests[req].finish === 0 ? 'is-missing' : 'is-met'"
							>
								{{ $t('pages.quests.require_quest', { id: req }) }}
							</span>
							<span v-else-if="key === 'level_minier'" :class="user.lvl.mine.l < req ? 'is-missing' : 'is-met'">
								{{ $t('pages.quests.require_mine', { level: req }) }}
							</span>
							<span v-else-if="key === 'level_raid'" :class="user.lvl.raid.l < req ? 'is-missing' : 'is-met'">
								{{ $t('pages.quests.require_raid', { level: req }) }}
							</span>
						</template>
					</div>
				</div>
				<span class="quest-status">
					<CheckIcon v-if="quest.finish" aria-hidden="true"/>
					<LockIcon v-else-if="!quest.available" aria-hidden="true"/>
					<TargetIcon v-else aria-hidden="true"/>
					{{ $t(quest.finish ? 'pages.quests.completed' : quest.available ? 'pages.quests.available' : 'pages.quests.locked') }}
				</span>
				<RightIcon v-if="quest.available" class="quest-open" stroke-width="1.5" aria-hidden="true"/>
			</component>
			<div v-if="!page.items.length" class="quests-empty">{{ $t('pages.quests.empty') }}</div>
		</div>
	</div>
</template>

<script setup>
	import LockIcon from '~/images/icons/lock.svg?component';
	import TargetIcon from '~/images/icons/target.svg?component';
	import CheckIcon from '~/images/icons/check.svg?component';
	import RightIcon from '~/images/icons/right.svg?component';
	import useState from '~/composables/useState.js';
	import { Head, Link } from '@inertiajs/vue3';
	import { computed } from 'vue';

	defineOptions({
		layout: {
			view: {
				resources: false
			}
		}
	});

	const props = defineProps({
		page: Object,
	});

	const state = useState();
	const user = computed(() => state.user);
	const completed = computed(() => props.page.items.filter((quest) => quest.finish).length);
</script>