<template>
	<tr class="galaxy-row" :class="{ 'is-empty': !item, 'is-own': item?.user?.id === currentUser.id, 'is-destroyed': item?.planet?.destruyed }">
		<td class="th galaxy-position">{{ planet }}</td>
		<td class="th img">
			<Popper popper-class="galaxy-tooltip" v-if="item && !item['planet']['destruyed']">
				<template #content>
					<PlanetTooltip :item="item" :is-vacation="isVacation" :is-own="item.user?.id === currentUser.id" :can-phalanx="user.phalanx > 0"/>
				</template>
				<img :src="'/assets/images/planeten/small/s_' + item['planet']['image'] + '.jpg'" width="34" height="34" alt="">
			</Popper>
		</td>
		<td class="th galaxy-planet-name">
			<div v-if="item && !item['planet']['destruyed']">
				<span v-if="item['planet']['active'] <= 10" class="star">(*)</span>
				<span v-else-if="item['planet']['active'] < 60" class="star">({{ Math.floor(item['planet']['active']) }})</span>
				<span class="galaxy-planet-label">{{ item['planet']['name'] }}</span>
			</div>
			<div v-else-if="item && item['planet']['destruyed']">
				{{ $t('pages.galaxy.planet_destruyed') }}
			</div>
		</td>
		<td class="th img whitespace-nowrap">
			<Popper popper-class="galaxy-tooltip" v-if="item && item['moon'] && !item['moon']['destruyed']">
				<template #content>
					<MoonTooltip :item="item" :is-vacation="isVacation" :is-own="item.user?.id === currentUser.id" :can-destroy="currentPlanet.units.dearth_star > 0"/>
				</template>
				<img src="/assets/images/planeten/small/s_mond.jpg" height="34" width="34" alt="">
			</Popper>
			<span v-if="item && item['moon'] && item['moon']['destruyed']">~</span>
		</td>
		<td class="th galaxy-debris" :class="[debris_class]">
			<Popper popper-class="galaxy-tooltip" v-if="item && (item.debris.metal || item.debris.crystal)">
				<template #content>
					<DebrisTooltip :item="item" :is-vacation="isVacation" :can-recycle="currentPlanet.units.recycler > 0" @collect="debris"/>
				</template>
				<img src="/assets/images/planeten/debris.jpg" height="22" width="22" alt="">
			</Popper>
		</td>
		<td class="th galaxy-player">
			<Popper popper-class="galaxy-tooltip" v-if="item && item.user && !item['planet']['destruyed']">
				<template #content>
					<PlayerTooltip :item="item" :is-own="item.user.id === currentUser.id"/>
				</template>
				<div>
					<span :class="[user_status_class]">{{ item.user['name'] }}</span>
					<span v-if="user_status" :class="[user_status_class]">
						<span style="color: var(--text-color)">(</span>
						<span v-if="user_status === 'UG' || user_status === 'G'">
							<Link href="/blocked" :class="[user_status_class]">{{ user_status }}</Link>
						</span>
						<span v-else>{{ user_status }}</span>
						<span style="color: var(--text-color)">)</span>
					</span>
					<span v-if="item.user['role'] === 'admin'" class="negative">A</span>
					<span v-if="item.user['role'] === 'super-operator'" class="neutral">SGo</span>
					<span v-if="item.user['role'] === 'operator'" class="positive">Go</span>
				</div>
			</Popper>
		</td>
		<td class="th">
			<Link v-if="item && !item['planet']['destruyed'] && item.user && item.user.race" :href="'/info/70' + item.user.race" :aria-label="$t('races.' + item.user.race)" :title="$t('races.' + item.user.race)">
				<RaceIcon :code="item.user.race" class="inline-block align-middle" :style="{ color: 'var(--faction-' + item.user.race + '-color)' }" width="20" height="20" aria-hidden="true" focusable="false"/>
			</Link>
		</td>
		<td class="th">
			<Popper popper-class="galaxy-tooltip" v-if="item && !item['planet']['destruyed'] && item['alliance']">
				<template #content>
					<AllianceTooltip :item="item"/>
				</template>
				<span :class="{ allymember: currentUser['alliance']?.id === item['alliance']['id'] }">{{ item['alliance']['tag'] }}</span>
			</Popper>
			<div v-if="item && item['alliance'] && currentUser['alliance']?.id !== item['alliance']['id']">
				<small v-if="item['alliance']['diplomacy'] === 0">[{{ $t('alliance.diplomacy_status.0') }}]</small>
				<small v-if="item['alliance']['diplomacy'] === 1" class="neutral">[{{ $t('alliance.diplomacy_status.1') }}]</small>
				<small v-if="item['alliance']['diplomacy'] === 2" class="positive">[{{ $t('alliance.diplomacy_status.2') }}]</small>
				<small v-if="item['alliance']['diplomacy'] === 3" class="negative">[{{ $t('alliance.diplomacy_status.3') }}]</small>
			</div>
		</td>
		<td class="th whitespace-nowrap">
			<div class="actions">
				<template v-if="item && !item['planet']['destruyed'] && item.user && item.user['id'] !== currentUser['id']">
					<Popper :content="$t('send_message')">
						<SendMessagePopup :aria-label="$t('send_message')" :id="item.user['id']">
							<GalaxyIcon type="message"/>
						</SendMessagePopup>
					</Popper>
					<Popper :content="$t('pages.galaxy.actions_friend')">
						<Link :href="'/friends/new/' + item.user['id']">
							<GalaxyIcon type="friend"/>
						</Link>
					</Popper>
					<Popper v-if="!isVacation && user['missile']" :content="$t('pages.galaxy.actions_rockets')">
						<button type="button" @click="$emit('sendMissile')" :aria-label="$t('pages.galaxy.actions_rockets')">
							<GalaxyIcon type="missile"/>
						</button>
					</Popper>
					<Popper popper-class="galaxy-tooltip" v-if="!isVacation && currentPlanet['units']['spy_sonde'] && !item.user['vacation']">
						<template #content>
							<div class="text-center flex flex-col gap-2">
								<div>
									<input type="text" class="w-full min-w-full" v-model.number="spyCount">
								</div>
								<div>
									<button @click.prevent="spy(item['planet']['type'], $event)" type="button" class="button w-full">
										{{ $t('pages.galaxy.actions_spy_planet') }}
									</button>
								</div>
								<div>
									<button v-if="item['moon'] && !item['moon']['destruyed']" @click.prevent="spy(3, $event)" type="button" class="button w-full">
										{{ $t('pages.galaxy.actions_spy_moon') }}
									</button>
								</div>
							</div>
						</template>
						<button type="button" class="galaxy-spy-trigger" :aria-label="$t('pages.galaxy.espionage')">
							<GalaxyIcon type="spy"/>
						</button>
					</Popper>
					<Popper :content="$t('pages.galaxy.actions_player_info')">
						<ModalLink navigate :href="'/players/' + item.user['id']">
							<GalaxyIcon type="player"/>
						</ModalLink>
					</Popper>
					<Popper :content="$t('pages.galaxy.actions_bookmarks')">
						<Link :href="'/fleet/shortcut/create?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=' + item['planet']['type']">
							<GalaxyIcon type="bookmark"/>
						</Link>
					</Popper>
				</template>
				<Popper v-if="!isVacation && !item && currentPlanet['units']['colonizer']" :content="$t('fleet_mission.7')">
					<Link :href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&mission=7'">
						<GalaxyIcon type="colonize"/>
					</Link>
				</Popper>
			</div>
		</td>
	</tr>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import GalaxyIcon from './GalaxyIcon.vue';
	import SendMessagePopup from '../Messages/SendMessagePopup.vue';
	import { sendMission } from '~/utils/fleet.js';
	import { computed, ref } from 'vue';
	import dayjs from 'dayjs';
	import { Link } from '@inertiajs/vue3';
	import { ModalLink } from '@inertiaui/modal-vue';
	import Popper from '~/components/Popper.vue';
	import PlanetTooltip from './PlanetTooltip.vue';
	import MoonTooltip from './MoonTooltip.vue';
	import DebrisTooltip from './DebrisTooltip.vue';
	import PlayerTooltip from './PlayerTooltip.vue';
	import AllianceTooltip from './AllianceTooltip.vue';
	import RaceIcon from '~/components/RaceIcon.vue';

	const {
		/** @type { Number } */
		galaxy,
		/** @type { Number } */
		system,
		/** @type { Number } */
		planet,
		/** @type {{ id: Number, position, planet: Object, debris: { metal: Number, crystal: Number }, moon: Object, user: Object, alliance: Object }} */
		item
	} = defineProps({
		galaxy: {
			type: Number,
			default: 1
		},
		system: {
			type: Number,
			default: 1
		},
		planet: {
			type: Number,
			default: 1
		},
		item: {
			type: Object,
			default: null,
		},
		user: {
			type: Object,
		},
	});

	const state = useState();
	const currentUser = computed(() => state.user);
	const currentPlanet = computed(() => state.planet);
	const isVacation = computed(() => currentUser.value !== null && currentUser.value.vacation !== null);

	const spyCount = ref(parseInt(currentUser.value['options']['spy']) || 1);

	const user_status = computed(() => {
		if (!item?.user) {
			return '';
		}

		let CurrentPoints = currentUser.value['points']['total'] || 0;
		let RowUserPoints = item.user?.['stats']?.['points'] || 0;

		if (!RowUserPoints) {
			RowUserPoints = 0;
		}

		if (item.user['blocked'] && dayjs(item.user['blocked']).diff() > 0) {
			if (item.user['vacation'] > 0) {
				return 'UG';
			} else {
				return 'G';
			}
		} else if (item.user['vacation'] > 0)
			return 'U';
		else if (item.user['online'] === 1) {
			return 'i';
		} else if (item.user['online'] === 2) {
			return 'iI';
		} else if (RowUserPoints * 5 < CurrentPoints || RowUserPoints <= 5000) {
			return 'N';
		} else if (RowUserPoints > CurrentPoints * 5) {
			return 'S';
		} else {
			return '';
		}
	})

	const user_status_class = computed(() => {
		if (user_status.value === 'UG') {
			return 'vacation';
		} else if (user_status.value === 'G') {
			return 'blocked';
		} else if (user_status.value === 'U') {
			return 'vacation';
		} else if (user_status.value === 'i') {
			return 'inactive';
		} else if (user_status.value === 'iI') {
			return 'longinactive';
		} else if (user_status.value === 'N') {
			return 'noob';
		} else if (user_status.value === 'S') {
			return 'strong';
		}

		return '';
	});

	const debris_class = computed(() => {
		if (!item) {
			return '';
		}

		let debris = parseInt(item.debris.metal) + parseInt(item.debris.crystal);

		if (debris >= 10000000) {
			return 'debris_100';
		} else if (debris >= 1000000) {
			return 'debris_50';
		} else if (debris >= 100000) {
			return 'debris_0';
		}

		return '';
	});

	async function spy (planet_type, event) {
		event.target.setAttribute('disabled', 'disabled')

		await sendMission(6, galaxy, system, planet, planet_type, spyCount.value);

		event.target.setAttribute('disabled', '');
	}

	function debris () {
		sendMission(8, galaxy, system, planet, 2, 0);
	}
</script>
