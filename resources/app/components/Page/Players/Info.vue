<template>
	<div class="game-page page-players">
		<header class="player-hero">
			<img class="player-avatar" :src="item.avatar" :alt="item.name">
			<div class="player-identity">
				<span class="game-eyebrow">{{ $t('pages.players.profile_heading') }}</span>
				<h1>{{ item.name }}</h1>
				<div class="player-race" v-if="item.race">
					<RaceIcon :code="item.race" aria-hidden="true"/>
					{{ $t('races.' + item.race) }}
				</div>
				<div v-if="user" class="player-actions">
					<UiButton :as="SendMessagePopup" :id="item.id">
						<SendIcon aria-hidden="true"/>
						{{ $t('send_message') }}
					</UiButton>
					<UiButton :as="Link" variant="secondary" :href="'/friends/new/' + item.id">
						<UserAddIcon aria-hidden="true"/>
						{{ $t('pages.players.add_friend_title') }}
					</UiButton>
				</div>
			</div>
		</header>
		<UiPanel class="game-panel">
			<dl class="player-details">
				<div v-if="item.planet">
					<dt>{{ $t('pages.players.field_planet') }}</dt>
					<dd>
						<Link :href="'/galaxy?galaxy=' + item.planet.galaxy + '&system=' + item.planet.system">{{ item.planet.name }} <span class="game-coordinates">[{{ item.planet.galaxy }}:{{ item.planet.system }}:{{ item.planet.planet }}]</span></Link>
					</dd>
				</div>
				<div v-if="item.alliance">
					<dt>{{ $t('pages.players.field_alliance') }}</dt>
					<dd>
						<Link :href="'/alliance/info/' + item.alliance.id">{{ item.alliance.name }}</Link>
					</dd>
				</div>
				<div>
					<dt>{{ $t('pages.players.field_gender') }}</dt>
					<dd>{{ $t(item.sex === 2 ? 'pages.players.gender_female' : 'pages.players.gender_male') }}</dd>
				</div>
			</dl>
			<div class="player-ranks">
				<div>
					<img :src="'/assets/images/ranks/m' + item.level.mine + '.png'" alt="">
					<span>{{ $t('pages.players.rank_industrial_branch') }}</span>
				</div>
				<div>
					<img :src="'/assets/images/ranks/f' + item.level.raid + '.png'" alt="">
					<span>{{ $t('pages.players.rank_military_branch') }}</span>
				</div>
			</div>
		</UiPanel>
		<UiPanel v-if="item.stats" class="game-panel">
			<header class="game-panel-heading">
				<h2>{{ $t('pages.players.game_stats_title') }}</h2>
				<Link :href="'/players/' + item.id + '/stats'" class="game-text-link">{{ $t('pages.players.meta_stats_title') }} →</Link>
			</header>
			<table class="game-table">
				<thead>
					<tr>
						<th></th>
						<th>{{ $t('pages.players.table_points') }}</th>
						<th>{{ $t('pages.players.table_rank') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="stat in stats" :key="stat.key" :class="{ 'is-total': stat.key === 'total' }">
						<th>{{ $t('pages.players.' + stat.label) }}</th>
						<td>{{ $formatNumber(item.stats[stat.key + '_points']) }}</td>
						<td>{{ $formatNumber(item.stats[stat.key + '_rank']) }}</td>
					</tr>
				</tbody>
			</table>
		</UiPanel>
		<UiPanel :title="$t('pages.players.battle_stats_title')" class="game-panel">
			<table class="game-table">
				<thead>
					<tr>
						<th></th>
						<th>{{ $t('pages.players.table_sum') }}</th>
						<th>{{ $t('pages.players.table_percent') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-if="item.fights.wons > 0" class="player-wins">
						<th>{{ $t('pages.players.fight_wins') }}</th>
						<td>{{ $formatNumber(item.fights.wons) }}</td>
						<td>{{ Math.round(100 * item.fights.wons / (item.fights.wons + item.fights.loos)) }} %</td>
					</tr>
					<tr v-if="item.fights.loos > 0" class="player-losses">
						<th>{{ $t('pages.players.fight_losses') }}</th>
						<td>{{ $formatNumber(item.fights.loos) }}</td>
						<td>{{ Math.round(100 * item.fights.loos / (item.fights.wons + item.fights.loos)) }} %</td>
					</tr>
					<tr class="is-total">
						<th>{{ $t('pages.players.fight_total_sorties') }}</th>
						<td>{{ $formatNumber(item.fights.total) }}</td>
						<td>{{ item.fights.total ? '100 %' : '—' }}</td>
					</tr>
				</tbody>
			</table>
		</UiPanel>
		<UiPanel :title="$t('pages.players.about')" v-if="item.about" class="game-panel">
			<div class="game-prose">
				<TextViewer :text="item.about"/>
			</div>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiPanel } from '~/components/UI';
	import RaceIcon from '~/components/RaceIcon.vue';
	import SendIcon from '~/images/icons/send.svg?component';
	import UserAddIcon from '~/images/icons/user-add.svg?component';
	import useState from '~/composables/useState.js';
	import SendMessagePopup from '../Messages/SendMessagePopup.vue';
	import { Link } from '@inertiajs/vue3'
	import { computed } from 'vue';
	import TextViewer from '~/components/TextViewer.vue';

	defineProps({
		item: {
			type: Object
		}
	});

	const stats = [{ key: 'build', label: 'stat_buildings' }, { key: 'tech', label: 'stat_research' }, { key: 'fleet', label: 'stat_fleet' }, { key: 'defs', label: 'stat_defense' }, { key: 'total', label: 'stat_total' }];

	const state = useState();
	const user = computed(() => state.user);
</script>