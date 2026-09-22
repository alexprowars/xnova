<template>
	<li class="tech-dependency">
		<div class="tech-dependency-card" :class="available ? 'is-ready' : 'is-missing'">
			<img v-if="item" :src="'/assets/images/elements/' + item.id + '.webp'" alt="" width="40" height="40" loading="lazy">
			<RaceIcon
				v-else-if="isRace"
				:code="requirement.level"
				:style="{ color: 'var(--faction-' + requirement.level + '-color)' }"
				width="40" height="40"
				aria-hidden="true"
				focusable="false"
			/>
			<span v-else class="tech-dependency-placeholder" aria-hidden="true">◇</span>
			<div class="tech-dependency-info">
				<ModalLink v-if="item" navigate :href="'/info/' + item.id" aria-haspopup="dialog">{{ item.name }}</ModalLink>
				<span v-else>{{ requirement.name }}</span>
				<div class="tech-dependency-levels">
					<CheckIcon v-if="available" stroke-width="2.25" aria-hidden="true"/>
					<svg v-else viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
						<circle cx="8" cy="8" r="6"/>
						<path d="M8 4v5m0 2v1"/>
					</svg>
					<span v-if="isRace">{{ $t('races.' + requirement.level) }}</span>
					<span v-else :title="$t('pages.techtree.level_hint')"><b>{{ requirement.current }}</b> / {{ requirement.level }}</span>
					<span v-if="requirement.queue > 0" class="tech-queued">
						+{{ requirement.queue }} {{ $t('pages.techtree.in_queue') }}
					</span>
				</div>
			</div>
			<button v-if="children.length" type="button" class="tech-branch-toggle button is-secondary icon-button" @click="expanded = !expanded" :aria-expanded="expanded">
				<svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
					<path :d="expanded ? 'm4 6 4 4 4-4' : 'm6 4 4 4-4 4'"/>
				</svg>
			</button>
		</div>
		<ul v-if="children.length && expanded" class="tech-dependency-children">
			<DependencyNode v-for="child in children" :key="child.id" :requirement="child" :items="items"/>
		</ul>
	</li>
</template>

<script setup>
	import CheckIcon from '~/images/icons/check.svg?component';
	import { computed, ref } from 'vue';
	import { ModalLink } from '@inertiaui/modal-vue';
	import useState from '~/composables/useState.js';
	import RaceIcon from '~/components/RaceIcon.vue';

	const props = defineProps({
		requirement: Object,
		items: Array,
	});
	const state = useState();
	const item = computed(() => props.items.find((item) => item.id === props.requirement.id));
	const children = computed(() => item.value?.requirments || []);
	const expanded = ref(!children.value.every(isBranchComplete));
	const isRace = computed(() => props.requirement.id === 'race');
	const available = computed(() => isRequirementMet(props.requirement));

	function isRequirementMet(requirement) {
		return requirement.id === 'race' ? state.user.race === requirement.level : requirement.current >= requirement.level;
	}

	function isBranchComplete(requirement) {
		if (!isRequirementMet(requirement)) {
			return false;
		}

		const requirements = props.items.find((item) => item.id === requirement.id)?.requirments || [];

		return requirements.every(isBranchComplete);
	}
</script>