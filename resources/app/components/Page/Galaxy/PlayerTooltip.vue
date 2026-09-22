<template>
	<div class="galaxy-tooltip-card galaxy-profile-card">
		<div class="galaxy-tooltip-summary">
			<img v-if="avatar" :src="avatar" class="galaxy-tooltip-image" width="64" height="64" alt="">
			<div v-else class="galaxy-profile-placeholder" aria-hidden="true">
				<GalaxyIcon type="player"/>
			</div>
			<div class="galaxy-tooltip-details">
				<span class="galaxy-tooltip-type">{{ $t('galaxy_player.title') }}</span>
				<strong class="galaxy-tooltip-title">{{ item.user.name }}</strong>
				<span v-if="item.user.stats?.rank > 0" class="galaxy-tooltip-meta">{{ $t('galaxy_player.rank', { rank: $formatNumber(item.user.stats.rank) }) }}</span>
			</div>
		</div>
		<div class="galaxy-tooltip-actions">
			<Link v-if="!isOwn" :href="'/messages/write/' + item.user.id" class="button is-secondary">
				<GalaxyIcon type="message"/>
				{{ $t('send_message') }}
			</Link>
			<Link :href="'/friends/new/' + item.user.id" class="button is-secondary">
				<GalaxyIcon type="friend"/>
				{{ $t('pages.galaxy.actions_friend') }}
			</Link>
			<Link :href="'/stats/players?page=' + statPage + '&id=' + item.user.id" class="button is-secondary">
				<svg class="icon" fill="currentColor" aria-hidden="true" focusable="false"><use xlink:href="/assets/images/symbols.svg#icon-statistics"/></svg>
				{{ $t('menu.stats') }}
			</Link>
		</div>
	</div>
</template>

<script setup>
	import { computed } from 'vue';
	import { Link } from '@inertiajs/vue3';
	import GalaxyIcon from './GalaxyIcon.vue';

	const props = defineProps({
		item: Object,
		isOwn: Boolean,
	});

	const avatar = computed(() => {
		const user = props.item.user;

		if (user.image) {
			return user.image;
		}

		if (user.avatar) {
			return user.avatar !== 99
				? '/assets/images/faces/' + user.sex + '/' + user.avatar + 's.png'
				: '/assets/avatars/upload_' + user.id + '.jpg';
		}

		return '';
	});

	const statPage = computed(() => Math.max(1, Math.ceil((props.item.user.stats?.rank || 0) / 100)));
</script>
