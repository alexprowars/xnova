<template>
	<div class="page-players">
		<div class="page-players-main block">
			<div class="title">{{ $t('pages.players.profile_heading') }}</div>
			<div class="content">
				<div class="block-table page-players">
					<div class="grid grid-cols-6 gap-1.5 divide-x-0!">
						<div class="col-span-2 text-center flex flex-col gap-1.5">
							<div><img :src="item['avatar']" :alt="item['name']" width="100%"></div>
							<div v-if="user">
								<SendMessagePopup :title="$t('send_message')" :id="item['id']"/>
								<Link :href="'/friends/new/' + item['id']" :title="$t('pages.players.add_friend_title')">
									<span class='sprite skin_b'></span>
								</Link>
							</div>
						</div>
						<div class="col-span-3">
							<div class="">
								<div class="grid grid-cols-3 p-2">
									<div>{{ $t('pages.players.field_login') }}</div>
									<div class="col-span-2">{{ item['name'] }}</div>
								</div>
								<div v-if="item['planet']" class="grid grid-cols-3 p-2">
									<div>{{ $t('pages.players.field_planet') }}</div>
									<div class="col-span-2">
										<Link :href="'/galaxy?galaxy=' + item['planet']['galaxy'] + '&system=' + item['planet']['system']" style="font-weight:normal">
											{{ item['planet']['name'] }} [{{ item['planet']['galaxy'] }}:{{ item['planet']['system'] }}:{{ item['planet']['planet'] }}]
										</Link>
									</div>
								</div>
								<div v-if="item['alliance']" class="grid grid-cols-3 p-2">
									<div>{{ $t('pages.players.field_alliance') }}</div>
									<div class="col-span-2">
										<Link :href="'/alliance/info/' + item['alliance']['id']">
											{{ item['alliance']['name'] }}
										</Link>
									</div>
								</div>
								<div class="grid grid-cols-3 p-2">
									<div>{{ $t('pages.players.field_gender') }}</div>
									<div class="col-span-2">{{ item['sex'] === 2 ? $t('pages.players.gender_female') : $t('pages.players.gender_male') }}</div>
								</div>
							</div>
							<div v-if="item['race'] !== 0" class="p-2">
								<img :src="'/assets/images/skin/race' + item['race'] + '.gif'" alt="">
							</div>
						</div>
						<div class="col-span-1 text-right pt-2">
							<img :src="'/assets/images/ranks/m' + item['level']['mine'] + '.png'" :alt="$t('pages.players.rank_industrial_branch')" v-tooltip="$t('pages.players.rank_industrial_branch')">
							<br>
							<img :src="'/assets/images/ranks/f' + item['level']['raid'] + '.png'" :alt="$t('pages.players.rank_military_branch')" v-tooltip="$t('pages.players.rank_military_branch')">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div v-if="item['stats']" class="page-players-stats block">
			<div class="title">{{ $t('pages.players.game_stats_title') }}</div>
			<div class="content">
				<div class="block-table text-center">
					<div class="grid grid-cols-3">
						<div class="c">&nbsp;</div>
						<div class="c">{{ $t('pages.players.table_points') }}</div>
						<div class="c">{{ $t('pages.players.table_rank') }}</div>
					</div>
					<div class="grid grid-cols-3">
						<div class="c">{{ $t('pages.players.stat_buildings') }}</div>
						<div class="th">{{ $formatNumber(item['stats']['build_points']) }}</div>
						<div class="th">{{ $formatNumber(item['stats']['build_rank']) }}</div>
					</div>
					<div class="grid grid-cols-3">
						<div class="c">{{ $t('pages.players.stat_research') }}</div>
						<div class="th">{{ $formatNumber(item['stats']['tech_points']) }}</div>
						<div class="th">{{ $formatNumber(item['stats']['tech_rank']) }}</div>
					</div>
					<div class="grid grid-cols-3">
						<div class="c">{{ $t('pages.players.stat_fleet') }}</div>
						<div class="th">{{ $formatNumber(item['stats']['fleet_points']) }}</div>
						<div class="th">{{ $formatNumber(item['stats']['fleet_rank']) }}</div>
					</div>
					<div class="grid grid-cols-3">
						<div class="c">{{ $t('pages.players.stat_defense') }}</div>
						<div class="th">{{ $formatNumber(item['stats']['defs_points']) }}</div>
						<div class="th">{{ $formatNumber(item['stats']['defs_rank']) }}</div>
					</div>
					<div class="grid grid-cols-3">
						<div class="c">{{ $t('pages.players.stat_total') }}</div>
						<div class="th">{{ $formatNumber(item['stats']['total_points']) }}</div>
						<div class="th">{{ $formatNumber(item['stats']['total_rank']) }}</div>
					</div>
				</div>
			</div>
		</div>
		<div class="page-players-stats block">
			<div class="title">{{ $t('pages.players.battle_stats_title') }}</div>
			<div class="content">
				<div class="block-table text-center">
					<div class="grid grid-cols-3">
						<div class="c">&nbsp;</div>
						<div class="c">{{ $t('pages.players.table_sum') }}</div>
						<div class="c">{{ $t('pages.players.table_percent') }}</div>
					</div>
					<div v-if="item['fights']['wons'] > 0" class="grid grid-cols-3">
						<div class="c">{{ $t('pages.players.fight_wins') }}</div>
						<div class="th"><b>{{ $formatNumber(item['fights']['wons']) }}</b></div>
						<div class="th">{{ Math.round((100 / (item['fights']['wons'] + item['fights']['loos'])) * item['fights']['wons']) }} %</div>
					</div>
					<div v-if="item['fights']['loos'] > 0" class="grid grid-cols-3">
						<div class="c">{{ $t('pages.players.fight_losses') }}</div>
						<div class="th"><b>{{ $formatNumber(item['fights']['loos']) }}</b></div>
						<div class="th">{{ Math.round((100 / (item['fights']['wons'] + item['fights']['loos'])) * item['fights']['loos']) }} %</div>
					</div>
					<div class="grid grid-cols-3">
						<div class="c">{{ $t('pages.players.fight_total_sorties') }}</div>
						<div class="th"><b>{{ $formatNumber(item['fights']['total']) }}</b></div>
						<div class="th">100 %</div>
					</div>
				</div>
			</div>
			<div v-if="item['about'].length" class="page-players-about block">
				<div class="content">
					<div class="block-table">
						<div class="grid">
							<div class="b">
								<TextViewer :text="item['about']"/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
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

	const state = useState();
	const user = computed(() => state.user);
</script>