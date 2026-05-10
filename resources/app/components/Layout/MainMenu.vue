<template>
	<nav class="main-menu">
		<a :class="{ active }" class="menu-toggle" @click.prevent="emit('toggle')">
			<span>
				<span class="first"></span>
				<span class="second"></span>
				<span class="third"></span>
			</span>
		</a>

		<div :class="{ active }" class="menu-sidebar">
			<ul>
				<MainMenuItem v-for="(item, i) in filteredItems" :item="item" :key="i"/>
			</ul>
		</div>
	</nav>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import MainMenuItem from './MainMenuItem.vue';
	import { computed } from 'vue';
	import { useI18n } from 'vue-i18n';

	defineProps({
		active: {
			type: Boolean,
			default: true
		}
	});

	const emit = defineEmits(['toggle']);
	const state = useState();
	const { t } = useI18n();

	const items = computed(() => [
		{ title: t('menu.overview'), url: '/overview' },
		{ title: t('menu.empire'), url: '/empire' },
		{ title: t('menu.galaxy'), url: '/galaxy' },
		{ title: t('menu.fleet'), url: '/fleet' },
		{ title: t('menu.buildings'), url: '/buildings' },
		{ title: t('menu.research'), url: '/research' },
		{ title: t('menu.shipyard'), url: '/shipyard' },
		{ title: t('menu.defense'), url: '/defense' },
		{ title: t('menu.resources'), url: '/resources' },
		{ title: t('menu.merchant'), url: '/merchant' },
		{ title: t('menu.officiers'), url: '/officiers' },
		{ title: t('menu.alliance'), url: '/alliance' },
		{ title: t('menu.friends'), url: '/friends' },
		{ title: t('menu.notes'), url: '/notes' },
		{ title: t('menu.records'), url: '/records' },
		{ title: t('menu.hall'), url: '/hall' },
		{ title: t('menu.logs'), url: '/logs' },
	]);

	const isVacation = computed(() => {
		return state.user && state.user.vacation !== null
	})

	const filteredItems = computed(() => {
		return items.value.filter((item) => typeof item['vacation'] === undefined || item['vacation'] !== isVacation.value);
	})
</script>