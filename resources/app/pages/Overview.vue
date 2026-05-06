<template>
	<Head :title="$t('menu.overview')"/>
	<div class="page-overview">
		<DailyBonus v-if="dailyBonus" :amount="dailyBonus"/>

		<div class="block">
			<div class="title">
				<div class="flex flex-col gap-2 sm:flex-row sm:justify-between">
					<div>
						{{ $t('planet_type.' + planet['type']) }} "{{ planet['name'] }}"
						<Link :href="'/galaxy?galaxy=' + planet['coordinates']['galaxy'] + '&system=' + planet['coordinates']['system']">
							[{{ planet['coordinates']['galaxy'] }}:{{ planet['coordinates']['system'] }}:{{ planet['coordinates']['planet'] }}]
						</Link>
						<Link v-if="!user.vacation" href="/overview/rename" :title="$t('pages.overview.planet_rename_hint')">
							({{ $t('pages.overview.planet_rename_action') }})
						</Link>
					</div>
					<div>
						<div class="float-sm-end"><clock/></div>
						<div class="clearfix sm:hidden"></div>
					</div>
				</div>
			</div>
			<div class="content">
				<div v-if="productionNotify" class="text-center mb-2">
					<span class="negative">
						<i18n-t keypath="pages.overview.resources_notify" scope="global">
							<template #link>
								<Link href="/resources">{{ $t('menu.resources') }}</Link>
							</template>
						</i18n-t>
					</span>
				</div>

				<div v-if="user['protection']" class="mb-2">
					<div class="text-center" v-html="$t('pages.overview.newbie_mode_notify')"></div>
				</div>

				<div v-if="fleets.length" class="mb-1.5">
					<Fleets :items="fleets"/>
				</div>

				<div class="overview grid sm:grid-cols-3 gap-1.5">
					<div>
						<div class="planet-image mb-2">
							<Link href="/overview/rename">
								<img :src="'/assets/images/planeten/' + planet['image'] + '.jpg'" alt="">
							</Link>
							<div v-if="planet['moon']" class="moon-image">
								<a href="" @click.prevent="changePlanet(planet['moon']['id'])" :title="planet['moon']['name']">
									<img :src="'/assets/images/planeten/' + planet['moon']['image'] + '.jpg'" height="50" width="50" alt="">
								</a>
							</div>
						</div>
						<div style="border: 1px solid rgb(153, 153, 255); width: 100%; margin: 0 auto;">
							<div id="CaseBarre" :style="'background-color: #'+(userFiledsPercent > 80 ? 'C00000' : (userFiledsPercent > 60 ? 'C0C000' : '00C000'))+'; width: '+userFiledsPercent+'%;  margin: 0 auto; text-align:center;'">
								<span style="color: #000000"><b>{{ userFiledsPercent }}%</b></span>
							</div>
						</div>
						<div class="page-overview-officiers">
							<Link v-for="item in user['officiers']" href="/officiers" class="page-overview-officiers-item">
								<Popper>
									<template #content>
										<div>
											{{ $t('officiers.' + item['code']) }}
											<br>
											<span v-if="item['date']">
												{{ $t('pages.overview.officier_active_until') }} <span class="positive">{{ $formatDate(item['date'], 'DD MMM HH:mm') }}</span>
											</span>
											<span v-else class="negative">{{ $t('pages.overview.officier_noactive') }}</span>
										</div>
									</template>
									<span class="officier" :class="[item['code'] + (item['date'] ? '_active' : '')]"></span>
								</Popper>
							</Link>
						</div>
					</div>
					<div>
						<div class="separator sm:hidden"></div>
						<div class="block-table text-center">
							<div class="grid">
								<div class="c">{{ $t('pages.overview.diameter') }}</div>
							</div>
							<div class="grid">
								<div class="th">
									{{ $formatNumber(planet['diameter']) }} {{ $t('km') }}
								</div>
							</div>
							<div class="grid">
								<div class="c">{{ $t('pages.overview.used') }}</div>
							</div>
							<div class="grid">
								<div class="th">
									<a :title="$t('pages.overview.used_fields')">{{ planet['field_used'] }}</a> / <a :title="$t('pages.overview.used_fields_max')">{{ planet['field_max'] }}</a> {{ $t('pages.overview.fields') }}
								</div>
							</div>
							<div class="grid">
								<div class="c">{{ $t('pages.overview.temp') }}</div>
							</div>
							<div class="grid">
								<div class="th">
									{{ $t('pages.overview.temp_from') }} {{ planet['temp_min'] }}&deg;C {{ $t('pages.overview.temp_until') }} {{ planet['temp_max'] }}&deg;C
								</div>
							</div>
							<div class="grid">
								<div class="c">
									{{ $t('pages.overview.debris') }}
									<a v-if="hasDebrisMission" @click.prevent="sendRecycle">
										({{ $t('pages.overview.recycle') }})
									</a>
								</div>
							</div>
							<div class="grid">
								<div class="th doubleth middle">
									<div>
										<img src="/assets/images/skin/s_metal.png" class="inline" alt="" align="absmiddle" v-tooltip="$t('resources.metal')">
										{{ $formatNumber(planet['debris']['metal']) }}
										/
										<img src="/assets/images/skin/s_crystal.png" class="inline" alt="" align="absmiddle" v-tooltip="$t('resources.crystal')">
										{{ $formatNumber(planet['debris']['crystal']) }}
									</div>
								</div>
							</div>
							<div class="grid">
								<div class="c">{{ $t('pages.overview.battles') }}</div>
							</div>
							<div class="grid">
								<div class="th middle">
									<img src="/assets/images/wins.gif" class="inline" alt="" align="absmiddle" v-tooltip="$t('pages.overview.battles_wins')">&nbsp;
									{{ user['raids']['win'] }}
									&nbsp;&nbsp;
									<img src="/assets/images/losses.gif" class="inline" alt="" align="absmiddle" v-tooltip="$t('pages.overview.battles_defeats')">&nbsp;
									{{ user['raids']['lost'] }}
								</div>
							</div>
							<div class="grid">
								<div class="th">
									{{ $t('pages.overview.fraction') }}: <Link href="/race">{{ $t('races.' + user['race']) }}</Link>
								</div>
							</div>
							<div class="grid">
								<div class="th">
									<Link href="/referrals">
										{{ host }}/?{{ user['id'] }}
									</Link>
									[{{ user['links'] }}]
								</div>
							</div>
						</div>
					</div>
					<div>
						<div class="separator sm:hidden"></div>
						<div class="block-table text-center">
							<div class="grid grid-cols-12">
								<div class="col-span-6 sm:col-span-5 c">{{ $t('pages.overview.player') }}:</div>
								<div class="col-span-6 sm:col-span-7 c" style="word-break: break-all;">
									<a :href="'/players/' + user['id']" @click.prevent="openPlayerPopup">{{ user['name'] }}</a>
								</div>
							</div>
							<div class="grid grid-cols-12 divide-x">
								<div class="col-span-6 sm:col-span-5 th">{{ $t('pages.overview.stats_build') }}:</div>
								<div class="col-span-6 sm:col-span-7 th">
									<span class="positive">{{ $formatNumber(user['points']['build']) }}</span>
								</div>
							</div>
							<div class="grid grid-cols-12 divide-x">
								<div class="col-span-6 sm:col-span-5 th">{{ $t('pages.overview.stats_fleet') }}:</div>
								<div class="col-span-6 sm:col-span-7 th">
									<span class="positive">{{ $formatNumber(user['points']['fleet']) }}</span>
								</div>
							</div>
							<div class="grid grid-cols-12 divide-x">
								<div class="col-span-6 sm:col-span-5 th">{{ $t('pages.overview.stats_defs') }}:</div>
								<div class="col-span-6 sm:col-span-7 th">
									<span class="positive">{{ $formatNumber(user['points']['defs']) }}</span>
								</div>
							</div>
							<div class="grid grid-cols-12 divide-x">
								<div class="col-span-6 sm:col-span-5 th">{{ $t('pages.overview.stats_tech') }}:</div>
								<div class="col-span-6 sm:col-span-7 th">
									<span class="positive">{{ $formatNumber(user['points']['tech']) }}</span>
								</div>
							</div>
							<div class="grid grid-cols-12 divide-x">
								<div class="col-span-6 sm:col-span-5 th">{{ $t('pages.overview.stats_total') }}:</div>
								<div class="col-span-6 sm:col-span-7 th">
									<span class="positive">{{ $formatNumber(user['points']['total']) }}</span>
								</div>
							</div>
							<div class="grid grid-cols-12 divide-x">
								<div class="col-span-6 sm:col-span-5 th">{{ $t('pages.overview.place') }}:</div>
								<div class="col-span-6 sm:col-span-7 th">
									<Link :href="'/stats?view=players&range=' + user['points']['place']">{{ user['points']['place'] }}</Link>
									<span :title="$t('pages.overview.place_diff')">
										<span v-if="user['points']['diff'] >= 1" class="positive">+{{ user['points']['diff'] }}</span>
										<span v-else-if="user['points']['diff'] < 0" class="negative">{{ user['points']['diff'] }}</span>
									</span>
								</div>
							</div>
							<div class="grid">
								<div class="c">{{ $t('pages.overview.mine_level') }}</div>
							</div>
							<div class="grid">
								<div class="th">
									{{ user['lvl']['mine']['l'] }} {{ $t('pages.overview.from') }} 100
								</div>
							</div>
							<div class="grid">
								<div class="th">
									{{ $formatNumber(user['lvl']['mine']['p']) }} / {{ $formatNumber(user['lvl']['mine']['u']) }} exp
								</div>
							</div>
							<div class="grid">
								<div class="c">{{ $t('pages.overview.raid_level') }}</div>
							</div>
							<div class="grid">
								<div class="th">
									{{ user['lvl']['raid']['l'] }} {{ $t('pages.overview.from') }} 100
								</div>
							</div>
							<div class="grid">
								<div class="th">
									{{ $formatNumber(user['lvl']['raid']['p']) }} / {{ $formatNumber(user['lvl']['raid']['u']) }} exp
								</div>
							</div>
						</div>
					</div>
				</div>
				<div v-if="queue.length > 0" class="block-table mt-1.5 border">
					<QueueRow v-for="(list, i) in queue" :key="i" :item="list"/>
				</div>
			</div>
		</div>

		<ChatList v-if="isMobile()"/>
	</div>
</template>

<script setup>
	import PlayerInfo from '../components/Page/Players/Info.vue';
	import Fleets from '../components/Page/Overview/Feets.vue';
	import Clock from '../components/Page/Overview/Clock.vue';
	import QueueRow from '../components/Page/Overview/QueueRow.vue';
	import DailyBonus from '../components/Page/Overview/DailyBonus.vue';
	import { sendMission } from '../utils/fleet';
	import { computed } from 'vue';
	import ChatList from '../components/Page/Overview/ChatList.vue';
	import { Link, usePage, router, Head } from '@inertiajs/vue3';
	import { changePlanet, isMobile } from '../utils/helpers.js';
	import { useWithLoadngIndicator } from '../composables/useLoading.js';
	import { openPopupModal } from '../composables/useModals.js';
	import Popper from '../components/Popper.vue';
	import { useApiGet } from '../composables/useApi.js';

	defineProps({
		dailyBonus: {
			type: Number,
			default: null,
		},
		fleets: {
			type: Array,
			default: () => [],
		}
	});

	const page = usePage();
	const user = computed(() => page.props.user);
	const planet = computed(() => page.props.planet);
	const queue = computed(() => page.props.queue);
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

	function openPlayerPopup () {
		useWithLoadngIndicator(async () => {
			const result = await useApiGet('/players/' + user.value['id']);

			await openPopupModal(PlayerInfo, { item: result });
		})
	}
</script>