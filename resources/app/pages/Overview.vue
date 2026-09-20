<template>
	<Head :title="$t('menu.overview')"/>
	<div>
		<div class="overview-heading">
			<div>
				<span class="eyebrow">{{ $t('interface.command') }}</span>
				<div class="overview-title-line">
					<h1>{{ planet.name }}</h1>
					<Link
						v-if="!user.vacation"
						href="/overview/rename"
						class="overview-rename"
						:title="$t('pages.overview.planet_rename_hint')"
						:aria-label="$t('pages.overview.planet_rename_hint')"
					>
						<svg
							viewBox="0 0 24 24"
							fill="none"
							stroke="currentColor"
							stroke-width="1.5"
							stroke-linecap="round"
							stroke-linejoin="round"
							aria-hidden="true"
						>
							<path d="m15 5 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15z"/>
						</svg>
					</Link>
				</div>
			</div>
			<Link :href="'/galaxy?galaxy=' + planet.coordinates.galaxy + '&system=' + planet.coordinates.system" class="overview-coordinates">[{{ planet.coordinates.galaxy }}:{{ planet.coordinates.system }}:{{ planet.coordinates.planet }}] <span aria-hidden="true">↗</span></Link>
		</div>
		<DailyBonus v-if="page.dailyBonus" :amount="page.dailyBonus"/>
		<div class="block overview-panel">
			<div class="content is-padded">
				<div v-if="productionNotify" class="overview-notice is-warning">
					<i18n-t keypath="pages.overview.resources_notify" scope="global">
						<template #link>
							<Link href="/resources">{{ $t('menu.resources') }}</Link>
						</template>
					</i18n-t>
				</div>
				<div v-if="user.protection" class="overview-notice" v-html="$t('pages.overview.newbie_mode_notify')"></div>
				<div v-if="page.fleets.length" class="mb-2">
					<Fleets :items="page.fleets"/>
				</div>
				<div class="overview-dashboard">
					<div class="overview-world">
						<div class="overview-world-image">
							<Link href="/overview/rename" :title="$t('pages.overview.planet_rename_hint')">
								<img :src="'/assets/images/planeten/' + planet.image + '.jpg'" :alt="planet.name">
							</Link>
							<button
								v-if="planet.moon"
								type="button"
								class="overview-moon"
								@click="changePlanet(planet.moon.id)"
								:title="planet.moon.name"
								:aria-label="planet.moon.name"
							>
								<img
									:src="'/assets/images/planeten/' + planet.moon.image + '.jpg'"
									:alt="planet.moon.name"
									width="40"
									height="40"
								>
							</button>
						</div>
						<div>
							<div class="planet-development">
								<div>
									<span>{{ $t('interface.fields') }}</span>
									<strong>{{ userFiledsPercent }}%</strong>
								</div>
								<progress
									:value="userFiledsPercent"
									max="100"
									:aria-label="$t('interface.fields')"
									:class="{ 'is-warning': userFiledsPercent > 60, 'is-full': userFiledsPercent > 80 }"
								/>
							</div>
							<div class="overview-section-label">{{ $t('menu.officiers') }}</div>
							<div class="page-overview-officiers">
								<Link
									v-for="item in user.officiers"
									:key="item.code"
									href="/officiers"
									class="page-overview-officiers-item"
									:class="{ 'is-active': item.date }"
									:aria-label="$t('officiers.' + item.code)"
								>
									<Popper>
										<template #content>
											<div>{{ $t('officiers.' + item.code) }}</div>
											<div v-if="item.date">
												{{ $t('pages.overview.officier_active_until') }}
												<span class="positive">{{ $formatDate(item.date, 'DD MMM HH:mm') }}</span>
											</div>
											<div v-else>{{ $t('pages.overview.officier_noactive') }}</div>
										</template>
										<span class="officier" :class="item.code + (item.date ? '_active' : '')" aria-hidden="true"></span>
									</Popper>
								</Link>
							</div>
						</div>
					</div>
					<div>
						<dl class="overview-details">
							<div>
								<dt>{{ $t('pages.overview.diameter') }}</dt>
								<dd>{{ $formatNumber(planet.diameter) }} {{ $t('km') }}</dd>
							</div>
							<div>
								<dt>{{ $t('pages.overview.used') }}</dt>
								<dd><span :title="$t('pages.overview.used_fields')">{{ planet.field_used }}</span> / <span :title="$t('pages.overview.used_fields_max')">{{ planet.field_max }}</span> {{ $t('pages.overview.fields') }}</dd>
							</div>
							<div>
								<dt>{{ $t('pages.overview.temp') }}</dt>
								<dd>
									{{ $t('pages.overview.temp_from') }} {{ planet.temp_min }}°C {{ $t('pages.overview.temp_until') }} {{ planet.temp_max }}°C
								</dd>
							</div>
						</dl>
						<div class="overview-fact-group">
							<div class="overview-section-header">
								<span class="overview-section-label">{{ $t('pages.overview.debris') }}</span>
								<button v-if="hasDebrisMission" type="button" class="overview-text-action" @click="sendRecycle">
									{{ $t('pages.overview.recycle') }}
									<span aria-hidden="true">↗</span>
								</button>
							</div>
							<div class="overview-debris">
								<span v-tooltip="$t('resources.metal')">
									<ResourceIcon code="metal" aria-hidden="true" focusable="false"/>
									<span class="sr-only">{{ $t('resources.metal') }}:</span>
									{{ $formatNumber(planet.debris.metal) }}
								</span>
								<span v-tooltip="$t('resources.crystal')">
									<ResourceIcon code="crystal" aria-hidden="true" focusable="false" class="crystal"/>
									<span class="sr-only">{{ $t('resources.crystal') }}:</span>
									{{ $formatNumber(planet.debris.crystal) }}
								</span>
							</div>
						</div>
						<div class="overview-fact-group">
							<div class="overview-section-label">{{ $t('pages.overview.battles') }}</div>
							<div class="overview-battles">
								<div>
									<span>{{ $t('pages.overview.battles_wins') }}</span>
									<strong>{{ $formatNumber(user.raids.win) }}</strong>
								</div>
								<div>
									<span>{{ $t('pages.overview.battles_defeats') }}</span>
									<strong>{{ $formatNumber(user.raids.lost) }}</strong>
								</div>
							</div>
						</div>
						<div class="overview-referrals">
							<span class="overview-section-label">{{ $t('pages.referrals.head_title') }}</span>
							<div>
								<Link href="/referrals">{{ host }}/?{{ user.id }}</Link>
								<span class="overview-referrals-count" :title="$t('pages.referrals.recruited_players_title')">
									{{ user.links }}
								</span>
							</div>
						</div>
					</div>
					<div class="overview-commander">
						<div>
							<span class="overview-section-label">{{ $t('pages.overview.player') }}</span>
							<ModalLink navigate :href="'/players/' + user.id" class="overview-player-name">{{ user.name }}</ModalLink>
							<div class="overview-player-race">
								{{ $t('pages.overview.fraction') }}
								:
								<Link href="/race">{{ $t('races.' + user.race) }}</Link>
							</div>
						</div>
						<div class="overview-score">
							<div>
								<span class="overview-section-label">{{ $t('pages.overview.stats_total') }}</span>
								<strong>{{ $formatNumber(user.points.total) }}</strong>
							</div>
							<div class="overview-rank">
								<span class="overview-section-label">{{ $t('pages.overview.place') }}</span>
								<div>
									<Link :href="'/stats/players?page=' + Math.max(1, Math.ceil(user.points.place / 100))">
										{{ user.points.place }}
									</Link>
									<span :title="$t('pages.overview.place_diff')">
										<span v-if="user.points.diff >= 1" class="positive">+{{ user.points.diff }}</span>
										<span v-else-if="user.points.diff < 0" class="negative">{{ user.points.diff }}</span>
									</span>
								</div>
							</div>
						</div>
						<dl class="overview-points">
							<div v-for="stat in pointStats" :key="stat.code">
								<dt>{{ $t('pages.overview.' + stat.label) }}</dt>
								<dd>{{ $formatNumber(user.points[stat.code]) }}</dd>
							</div>
						</dl>
						<div class="overview-levels">
							<div v-for="level in ['mine', 'raid']" :key="level" class="overview-level" :class="'overview-level-' + level">
								<div class="overview-section-header">
									<span>{{ $t('pages.overview.' + level + '_level') }}</span>
									<strong>{{ user.lvl[level].l }} <span>{{ $t('pages.overview.from') }} 100</span></strong>
								</div>
								<progress
									:value="user.lvl[level].p"
									:max="Math.max(user.lvl[level].u, 1)"
									:aria-label="$t('pages.overview.' + level + '_level')"
								/>
								<div class="overview-level-exp">
									{{ $formatNumber(user.lvl[level].p) }} / {{ $formatNumber(user.lvl[level].u) }} exp
								</div>
							</div>
						</div>
					</div>
				</div>
				<div v-if="queue.length > 0" class="overview-queue">
					<QueueRow v-for="(list, i) in queue" :key="i" :item="list"/>
				</div>
			</div>
		</div>
		<ChatList v-if="isMobile()"/>
	</div>
</template>

<script setup>
	import ResourceIcon from '~/components/ResourceIcon.vue';
	import useState from '~/composables/useState.js';
	import Fleets from '~/components/Page/Overview/Feets.vue';
	import QueueRow from '~/components/Page/Overview/QueueRow.vue';
	import DailyBonus from '~/components/Page/Overview/DailyBonus.vue';
	import { sendMission } from '~/utils/fleet';
	import { computed } from 'vue';
	import ChatList from '~/components/Page/Overview/ChatList.vue';
	import { Link, router, Head } from '@inertiajs/vue3';
	import { changePlanet, isMobile } from '~/utils/helpers.js';
	import Popper from '~/components/Popper.vue';
	import { ModalLink } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});

	const pointStats = [
		{ code: 'build', label: 'stats_build' },
		{ code: 'fleet', label: 'stats_fleet' },
		{ code: 'defs', label: 'stats_defs' },
		{ code: 'tech', label: 'stats_tech' },
	];

	const state = useState();
	const user = computed(() => state.user);
	const planet = computed(() => state.planet);
	const queue = computed(() => state.queue);
	const host = computed(() => import.meta.env.VITE_APP_URL || '');

	const userFiledsPercent = computed(() => {
		return Math.min(Math.floor(planet.value.field_used / planet.value.field_max * 100), 100);
	})

	const hasDebrisMission = computed(() => {
		return (planet.value['debris']['metal'] !== 0 || planet.value['debris']['crystal'] !== 0) && planet.value['units']['recycler'] > 0;
	})

	const productionNotify = computed(() => {
		if (user.value.vacation) {
			return false;
		}

		for (let res in planet.value['resources']) {
			if (typeof planet.value['resources'][res]['factor'] !== 'undefined' && planet.value['resources'][res]['factor'] < 1) {
				return true;
			}
		}

		return false;
	});

	async function sendRecycle () {
		await sendMission(
			8,
			planet.value['coordinates']['galaxy'],
			planet.value['coordinates']['system'],
			planet.value['coordinates']['planet'],
			2
		);

		router.reload();
	}
</script>