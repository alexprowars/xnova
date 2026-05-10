<template>
	<Head :title="$t('pages.race.head_title')"/>
	<div class="text-center">
		<div class="block-table raceSelect">
			<div class="grid grid-cols-2">
				<div class="k big">{{ $t('pages.race.faction_confederation') }}</div>
				<div class="k big">{{ $t('pages.race.faction_bionics') }}</div>
			</div>
			<div class="grid grid-cols-2">
				<div class="th text-left">
					<div class="text-center mt-2">
						<img src="/assets/images/skin/race1.gif" alt="">
					</div>
					<br>
					<div class="positive">{{ $t('pages.race.race_features') }}</div>
					<span style="color: #84CFEF"><span v-html="$t('pages.race.perks_race1')"></span>
					<br><br>{{ $t('pages.race.unique_ship_label') }}
					<span style="color: #adff2f">
						<ModalLink navigate href="info/220">{{ $t('pages.race.ship_race1_name') }}</ModalLink>
					</span> {{ $t('pages.race.ship_race1_desc') }}</span>
					<br><br>
				</div>
				<div class="th text-left">
					<div class="text-center mt-2">
						<img src="/assets/images/skin/race2.gif" alt="">
					</div>
					<br>
					<div class="positive">{{ $t('pages.race.race_features') }}</div>
					<span style="color: #84CFEF"><span v-html="$t('pages.race.perks_race2')"></span>
					<br><br>{{ $t('pages.race.unique_ship_label') }}
					<span style="color: #adff2f">
						<ModalLink navigate href="info/221">{{ $t('pages.race.ship_race2_name') }}</ModalLink>
					</span> {{ $t('pages.race.ship_race2_desc') }}</span>
					<br><br>
				</div>
			</div>
			<div class="grid grid-cols-2">
				<div class="k big">{{ $t('pages.race.faction_cylons') }}</div>
				<div class="k big">{{ $t('pages.race.faction_ancients') }}</div>
			</div>
			<div class="grid grid-cols-2">
				<div class="th text-left">
					<div class="text-center mt-2">
						<img src="/assets/images/skin/race3.gif" alt="">
					</div>
					<br>
					<div class="positive">{{ $t('pages.race.race_features') }}</div>
					<span style="color: #84CFEF"><span v-html="$t('pages.race.perks_race3')"></span>
					<br><br>{{ $t('pages.race.unique_ship_label') }}
					<span style="color: #adff2f">
						<ModalLink navigate href="info/222">{{ $t('pages.race.ship_race3_name') }}</ModalLink>
					</span> {{ $t('pages.race.ship_race3_desc') }}</span>
					<br><br>
				</div>
				<div class="th text-left">
					<div class="text-center mt-2">
						<img src="/assets/images/skin/race4.gif" alt="">
					</div>
					<br>
					<div class="positive">{{ $t('pages.race.race_features') }}</div>
					<span style="color: #84CFEF"><span v-html="$t('pages.race.perks_race4')"></span>
					<br><br>{{ $t('pages.race.unique_ship_label') }}
					<span style="color: #adff2f">
						<ModalLink navigate href="info/223">{{ $t('pages.race.ship_race4_name') }}</ModalLink>
					</span> {{ $t('pages.race.ship_race4_desc') }}</span>
					<br><br>
				</div>
			</div>
			<div v-if="page['change_available']">
				<div class="grid">
					<div class="k big">
						<span v-if="page['change']">
							{{ $t('pages.race.change_free', { count: page['change'] }) }}
						</span>
						<span v-else>
							{{ $t('pages.race.change_paid') }}
						</span>
					</div>
				</div>
				<div v-if="page['change_available']" class="th">
					{{ $t('pages.race.change_requirements') }}<br><br>
					<RaceChange/>
				</div>
			</div>
		</div>
	</div>
</template>

<script setup>
	import useState from '~/composables/useState.js';
	import RaceChange from '~/components/Page/Race/RaceChange.vue';
	import { computed, nextTick, onMounted } from 'vue';
	import { Head } from '@inertiajs/vue3';
	import { ModalLink, visitModal } from '@inertiaui/modal-vue';

	defineProps({
		page: Object,
	});

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	const state = useState();
	const user = computed(() => state.user);
	const race = computed(() => user.value?.race || 0);

	onMounted(() => {
		if (race.value) {
			return;
		}

		nextTick(() => {
			visitModal('/content/welcome');
		})
	});
</script>
