<template>
	<tr
		class="galaxy-row"
		:class="{ 'is-empty': !item, 'is-own': item?.user?.id === currentUser.id, 'is-destroyed': item?.planet?.destruyed }"
	>
		<td class="th galaxy-position">{{ planet }}</td>
		<td class="th img">
			<Popper popper-class="galaxy-tooltip" v-if="item && !item['planet']['destruyed']">
				<template #content>
					<div class="block-table w-80">
						<div class="grid">
							<div class="c">
								{{ $t('planet_type.' + item['planet']['type']) }} {{ item['planet']['name'] }} [{{ galaxy }}:{{ system }}:{{ planet }}]
							</div>
						</div>
						<div class="flex">
							<div class="th">
								<img
									:src="'/assets/images/planeten/small/s_' + item['planet']['image'] + '.jpg'"
									height="75"
									width="75"
									alt=""
								>
							</div>
							<div class="th grow middle flex-col" v-if="!isVacation && item.user">
								<div v-if="user['phalanx'] > 0">
									<Link :href="'/phalanx?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet" target="_blank">
										{{ $t('pages.galaxy.phalanx') }}
									</Link>
								</div>
								<template v-if="item.user['id'] !== currentUser['id']">
									<div>
										<Link
											:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=' + item['planet']['type'] + '&mission=1'"
										>
											{{ $t('fleet_mission.1') }}
										</Link>
									</div>
									<div>
										<Link
											:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=' + item['planet']['type'] + '&mission=5'"
										>
											{{ $t('fleet_mission.5') }}
										</Link>
									</div>
								</template>
								<div v-else>
									<Link
										v-if="item.user['id'] === currentUser['id']"
										:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=' + item['planet']['type'] + '&mission=4'"
									>
										{{ $t('fleet_mission.4') }}
									</Link>
								</div>
								<Link
									:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=' + item['planet']['type'] + '&mission=3'"
								>
									{{ $t('fleet_mission.3') }}
								</Link>
							</div>
						</div>
					</div>
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
					<table width="240">
						<tbody>
							<tr>
								<td class="c" colspan="2">
									{{ $t('planet_type.3') }}: {{ item['moon']['name'] }} [{{ galaxy }}:{{ system }}:{{ planet }}]
								</td>
							</tr>
							<tr>
								<td class="th" width="80">
									<img src="/assets/images/planeten/mond.jpg" height="75" width="75" alt="">
								</td>
								<td class="th">
									<div class="block-table">
										<div class="grid">
											<div class="c">{{ $t('pages.galaxy.moon_params') }}</div>
										</div>
										<div class="grid grid-cols-2">
											<div class="th">{{ $t('pages.galaxy.moon_diameter') }}</div>
											<div class="th">{{ $formatNumber(item['moon']['diameter']) }}</div>
										</div>
										<div class="grid grid-cols-2">
											<div class="th">{{ $t('pages.galaxy.moon_temp') }}</div>
											<div class="th">{{ item['moon']['temp'] }}</div>
										</div>
										<div class="grid">
											<div class="c">{{ $t('pages.galaxy.actions') }}</div>
										</div>
										<div v-if="!isVacation && item.user" class="grid">
											<div class="th text-center">
												<div v-if="item.user['id'] !== currentUser['id']">
													<Link
														:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=3&mission=1'"
													>
														{{ $t('fleet_mission.1') }}
													</Link>
													<br>
													<Link
														:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=3&mission=5'"
													>
														{{ $t('fleet_mission.5') }}
													</Link>
													<div v-if="currentPlanet['units']['dearth_star'] > 0">
														<Link
															:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=3&mission=9'"
														>
															{{ $t('fleet_mission.9') }}
														</Link>
													</div>
												</div>
												<div v-else>
													<Link
														:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=3&mission=4'"
													>
														{{ $t('fleet_mission.4') }}
													</Link>
												</div>
												<Link
													:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=3&mission=3'"
												>
													{{ $t('fleet_mission.3') }}
												</Link>
											</div>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</template>
				<img src="/assets/images/planeten/small/s_mond.jpg" height="34" width="34" alt="">
			</Popper>
			<span v-if="item && item['moon'] && item['moon']['destruyed']">~</span>
		</td>
		<td class="th galaxy-debris" :class="[debris_class]">
			<Popper popper-class="galaxy-tooltip" v-if="item && (item.debris.metal || item.debris.crystal)">
				<template #content>
					<table width="240">
						<tbody>
							<tr>
								<td class="c" colspan="2">
									{{ $t('pages.galaxy.debris') }}: [{{ galaxy }}:{{ system }}:{{ planet }}]
								</td>
							</tr>
							<tr>
								<td class="th" width="80">
									<img src="/assets/images/planeten/debris.jpg" height="75" width="75" alt="">
								</td>
								<td class="th">
									<div class="block-table text-center">
										<div class="grid">
											<div class="c">{{ $t('pages.galaxy.debris_resources') }}</div>
										</div>
										<div v-if="item.debris.metal" class="grid grid-cols-2">
											<div class="th">{{ $t('resources.metal') }}</div>
											<div class="th">{{ item.debris.metal }}</div>
										</div>
										<div v-if="item.debris.crystal" class="grid grid-cols-2">
											<div class="th">{{ $t('resources.crystal') }}</div>
											<div class="th">{{ item.debris.crystal }}</div>
										</div>
										<div v-if="!isVacation && currentPlanet['units']['recycler'] > 0" class="grid">
											<div class="th">
												<a @click.prevent="debris">{{ $t('pages.galaxy.debris_collect') }}</a>
											</div>
										</div>
										<div v-if="!isVacation" class="grid">
											<div class="th">
												<Link
													:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=2&mission=8'"
												>
													{{ $t('pages.galaxy.debris_send_fleet') }}
												</Link>
											</div>
										</div>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</template>
				<img src="/assets/images/planeten/debris.jpg" height="22" width="22" alt="">
			</Popper>
		</td>
		<td class="th galaxy-player">
			<Popper popper-class="galaxy-tooltip" v-if="item && item.user && !item['planet']['destruyed']">
				<template #content>
					<div class="block-table w-96">
						<div class="grid">
							<div class="c">
								{{ $t('galaxy_player.title') }}
								{{ item.user['name'] }}
								<template v-if="item.user['stats'] && item.user['stats']['rank'] > 0">
									, {{ $t('galaxy_player.rank', { rank: item.user['stats']['rank'] }) }}
								</template>
							</div>
						</div>
						<div class="flex">
							<div v-if="user_avatar" class="w-1/3">
								<img :src="user_avatar" class="object-cover object-center aspect-square" alt="">
							</div>
							<div class="w-2/3 th text-center flex flex-col justify-center gap-2">
								<Link v-if="item.user['id'] !== currentUser['id']" :href="'/messages/write/' + item.user['id']">
									{{ $t('send_message') }}
								</Link>
								<Link :href="'/friends/new/' + item.user['id']">{{ $t('pages.galaxy.actions_friend') }}</Link>
								<Link :href="'/stats/players?page=' + stat_page + '&id=' + item.user['id']">{{ $t('menu.stats') }}</Link>
							</div>
						</div>
					</div>
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
			<Link v-if="item && !item['planet']['destruyed'] && item.user && item.user['race']" :href="'/info/70' + item.user['race']">
				<img
					:src="'/assets/images/skin/race' + item.user['race'] + '.gif'"
					width="20"
					height="20"
					:alt="$t('races.' + item.user['race'])"
					:title="$t('races.' + item.user['race'])"
				>
			</Link>
		</td>
		<td class="th">
			<Popper popper-class="galaxy-tooltip" v-if="item && !item['planet']['destruyed'] && item['alliance']">
				<template #content>
					<div class="block-table w-80 text-center">
						<div class="grid">
							<div class="c">
								{{ $t('pages.galaxy.alliance', [item['alliance']['name'], item['alliance']['members']]) }}
							</div>
						</div>
						<div class="grid">
							<div class="th">
								<Link :href="'/alliance/info/' + item['alliance']['id']">{{ $t('pages.galaxy.alliance_info') }}</Link>
							</div>
						</div>
						<div class="grid">
							<div class="th">
								<Link href="/stats/alliances">{{ $t('pages.galaxy.alliance_stats') }}</Link>
							</div>
						</div>
					</div>
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
					<SendMessagePopup v-tooltip="$t('send_message')" :aria-label="$t('send_message')" :id="item.user['id']">
						<GalaxyIcon type="message"/>
					</SendMessagePopup>
					<Link
						:href="'/friends/new/' + item.user['id']"
						v-tooltip="$t('pages.galaxy.actions_friend')"
						:aria-label="$t('pages.galaxy.actions_friend')"
					>
						<GalaxyIcon type="friend"/>
					</Link>
					<button
						type="button"
						v-if="!isVacation && user['missile']"
						@click="$emit('sendMissile')"
						v-tooltip="$t('pages.galaxy.actions_rockets')"
						:aria-label="$t('pages.galaxy.actions_rockets')"
					>
						<GalaxyIcon type="missile"/>
					</button>
					<Popper
						popper-class="galaxy-tooltip"
						v-if="!isVacation && currentPlanet['units']['spy_sonde'] && !item.user['vacation']"
					>
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
									<button
										v-if="item['moon'] && !item['moon']['destruyed']"
										@click.prevent="spy(3, $event)"
										type="button"
										class="button w-full"
									>
										{{ $t('pages.galaxy.actions_spy_moon') }}
									</button>
								</div>
							</div>
						</template>
						<button type="button" class="galaxy-spy-trigger" :aria-label="$t('pages.galaxy.espionage')">
							<GalaxyIcon type="spy"/>
						</button>
					</Popper>
					<ModalLink
						navigate
						:href="'/players/' + item.user['id']"
						v-tooltip="$t('pages.galaxy.actions_player_info')"
						:aria-label="$t('pages.galaxy.actions_player_info')"
					>
						<GalaxyIcon type="player"/>
					</ModalLink>
					<Link
						:href="'/fleet/shortcut/create?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&type=' + item['planet']['type']"
						v-tooltip="$t('pages.galaxy.actions_bookmarks')"
						:aria-label="$t('pages.galaxy.actions_bookmarks')"
					>
						<GalaxyIcon type="bookmark"/>
					</Link>
				</template>
				<Link
					v-if="!isVacation && !item && currentPlanet['units']['colonizer']"
					:href="'/fleet?galaxy=' + galaxy + '&system=' + system + '&planet=' + planet + '&mission=7'"
					v-tooltip="$t('fleet_mission.7')"
					:aria-label="$t('fleet_mission.7')"
				>
					<GalaxyIcon type="colonize"/>
				</Link>
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

	const user_avatar = computed(() => {
		if (!item || !item.user) {
			return '';
		}

		if (item.user['image']) {
			return item.user['image'];
		} else if (item.user['avatar']) {
			if (item.user['avatar'] !== 99) {
				return '/assets/images/faces/' + item.user['sex'] + '/' + item.user['avatar'] + 's.png';
			} else {
				return '/assets/avatars/upload_' + item.user['id'] + '.jpg';
			}
		}

		return '';
	});

	const stat_page = computed(() => {
		if (!item?.user?.stats) {
			return 1;
		}

		return Math.max(1, Math.ceil(item.user.stats.rank / 100));
	})

	async function spy (planet_type, event) {
		event.target.setAttribute('disabled', 'disabled')

		await sendMission(6, galaxy, system, planet, planet_type, spyCount.value);

		event.target.setAttribute('disabled', '');
	}

	function debris () {
		sendMission(8, galaxy, system, planet, 2, 0);
	}
</script>