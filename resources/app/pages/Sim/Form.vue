<template>
	<Head :title="$t('pages.sim.title')"/>
	<div class="game-page page-sim">
		<UiHeading :title="$t('pages.sim.title')" class="game-heading">
			<template #actions>
				<span class="game-eyebrow">XNova SIM</span>
			</template>
		</UiHeading>
		<UiPanel class="game-panel">
			<div class="sim-controls">
				<label class="sim-attacker">
					{{ $t('pages.sim.attackers') }}
					<select v-model.number="attackers">
						<option v-for="i in page.slots.max" :key="i" :value="i">{{ i }}</option>
					</select>
				</label>
				<span>{{ $t('pages.sim.setup') }}</span>
				<label class="sim-defender">
					{{ $t('pages.sim.defenders') }}
					<select v-model.number="defenders">
						<option v-for="i in page.slots.max" :key="i" :value="i">{{ i }}</option>
					</select>
				</label>
			</div>
		</UiPanel>
		<UiPanel as="div" class="game-panel sim-table-wrap">
			<form method="get" action="/sim/report" name="form" ref="resultRef" autocomplete="off" target="_blank">
				<input type="hidden" name="r" value="">
			</form>
			<table ref="formRef" class="sim-table">
				<tbody>
					<tr>
						<td class="th">&nbsp;</td>
						<td class="th">{{ $t('pages.sim.lead') }}</td>
						<td v-for="i in Math.min(page.slots['max'], attackers) - 1" class="th">
							{{ $t('pages.sim.attacker') }} {{ i }}
						</td>
						<td class="th">{{ $t('pages.sim.planet') }}</td>
						<td v-for="i in Math.min(page.slots['max'], defenders) - 1" class="th">
							{{ $t('pages.sim.defender') }} {{ i }}
						</td>
					</tr>
					<tr>
						<td class="c" :colspan="cols">{{ $t('pages.sim.tech') }}</td>
					</tr>
					<tr v-for="techId in page.tech" align="center">
						<td class="th">{{ $t('tech.'+techId) }}</td>
						<td class="th" v-for="i in range(0, Math.min(page.slots['max'], attackers) - 1)">
							<input
								inputmode="numeric"
								class="number"
								:aria-label="$t('tech.' + techId) + ' · ' + $t('pages.sim.attacker') + ' ' + (i + 1)"
								:value="page.slots['attackers'][i] !== undefined && page.slots['attackers'][i][techId] !== undefined ? page.slots['attackers'][i][techId]['c'] : 0"
								type="text"
								:name="'gr'+i+'-'+techId"
								maxlength="2"
							>
						</td>
						<td class="th" v-for="i in range(0, Math.min(page.slots['max'], defenders) - 1)">
							<input
								inputmode="numeric"
								class="number"
								:aria-label="$t('tech.' + techId) + ' · ' + $t('pages.sim.defender') + ' ' + (i + 1)"
								:value="page.slots['defenders'][i] !== undefined && page.slots['defenders'][i][techId] !== undefined ? page.slots['defenders'][i][techId]['c'] : 0"
								type="text"
								:name="'gr'+(i +page.slots['max'])+'-'+techId"
								maxlength="2"
							>
						</td>
					</tr>
					<tr>
						<td class="c" :colspan="cols">{{ $t('pages.sim.fleet') }}</td>
					</tr>
					<tr v-for="fleetId in Object.keys($tm('tech')).filter((v) => v > 200 && v < 300)" align="center">
						<td class="th">{{ $t('tech.' + fleetId) }}</td>
						<td class="th" v-for="i in range(0, Math.min(page.slots['max'], attackers) - 1)">
							<template v-if="parseInt(fleetId) === 212">-</template>
							<input
								v-else
								inputmode="numeric"
								class="number"
								:aria-label="$t('tech.' + fleetId) + ' · ' + $t('pages.sim.attacker') + ' ' + (i + 1)"
								:value="page.slots['attackers'][i] !== undefined && page.slots['attackers'][i][fleetId] !== undefined ? page.slots['attackers'][i][fleetId]['c'] : 0"
								type="text"
								:name="'gr'+i+'-'+fleetId"
								maxlength="7"
							>
						</td>
						<td class="th" v-for="i in range(0, Math.min(page.slots['max'], defenders) - 1)">
							<input
								inputmode="numeric"
								class="number"
								:aria-label="$t('tech.' + fleetId) + ' · ' + $t('pages.sim.defender') + ' ' + (i + 1)"
								:value="page.slots['defenders'][i] !== undefined && page.slots['defenders'][i][fleetId] !== undefined ? page.slots['defenders'][i][fleetId]['c'] : 0"
								type="text"
								:name="'gr'+(i +page.slots['max'])+'-'+fleetId"
								maxlength="7"
							>
						</td>
					</tr>
					<tr>
						<td class="c" :colspan="cols">{{ $t('pages.sim.defense') }}</td>
					</tr>
					<tr v-for="fleetId in Object.keys($tm('tech')).filter((v) => v > 400 && v < 500)" align="center">
						<td class="th">{{ $t('tech.' + fleetId) }}</td>
						<td class="th" v-for="i in range(0, Math.min(page.slots['max'], attackers) - 1)">
							-
						</td>
						<td class="th" v-for="i in range(0, Math.min(page.slots['max'], defenders) - 1)">
							<template v-if="(parseInt(fleetId) === 407 || parseInt(fleetId) === 408) && i > 0">-</template>
							<input
								v-else
								inputmode="numeric"
								class="number"
								:aria-label="$t('tech.' + fleetId) + ' · ' + $t('pages.sim.defender') + ' ' + (i + 1)"
								:value="page.slots['defenders'][i] !== undefined && page.slots['defenders'][i][fleetId] !== undefined ? page.slots['defenders'][i][fleetId]['c'] : 0"
								type="text"
								:name="'gr'+(i +page.slots['max'])+'-'+fleetId"
								maxlength="7"
							>
						</td>
					</tr>
					<tr align="center">
						<td class="th">&nbsp;</td>
						<td class="th" v-for="i in range(0, Math.min(page.slots['max'], attackers) - 1)">
							<UiButton variant="secondary" @click="clear(i)">{{ $t('pages.sim.clear') }}</UiButton>
						</td>
						<td class="th" v-for="i in range(0, Math.min(page.slots['max'], defenders) - 1)">
							<UiButton variant="secondary" @click="clear(page.slots['max'] + i)">{{ $t('pages.sim.clear') }}</UiButton>
						</td>
					</tr>
					<tr>
						<td class="th text-center" :colspan="cols">
							<UiButton @click="calculate">{{ $t('pages.sim.calculate') }}</UiButton>
						</td>
					</tr>
				</tbody>
			</table>
		</UiPanel>
	</div>
</template>

<script setup>
	import { UiButton, UiHeading, UiPanel } from '~/components/UI';
	import { computed, ref, useTemplateRef } from 'vue';
	import { Head } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	defineProps({
		page: Object,
	});

	const attackers = ref(1);
	const defenders = ref(1);
	const formRef = useTemplateRef('formRef');
	const resultRef = useTemplateRef('resultRef');

	const cols = computed(() => {
		return attackers.value + defenders.value + 1;
	});

	function clear (index) {
		formRef.value.querySelectorAll('input[type=text][name^="gr'+index+'-"]').forEach((el) => el.value = '0');
	}

	function calculate () {
		let txt = '', tstr = '', tkey, tval;
		tkey = [];

		formRef.value.querySelectorAll('input[type="text"][name^="gr"]').forEach((el) => {
			if (el.value > 0) {
				tstr = el.name;
				tval = tstr.split("-");

				tval[0] = parseInt(tval[0].split('gr').join(''));

				if (tkey[tval[0]]) {
					tkey[tval[0]] += parseInt(tval[1]) + ',' + el.value + ';';
				} else {
					tkey[tval[0]] = parseInt(tval[1]) + ',' + el.value + ';';
				}
			}
		});

		if (tkey.length > 0) {
			for (let i = 0; i < tkey.length; i++) {
				if (tkey[i]) {
					txt += tkey[i] + '|';
				} else {
					txt += '|';
				}
			}
		}

		resultRef.value.querySelector('input').value = txt;
		resultRef.value.submit();
	}

	function range (min, max) {
		let array = [], j = 0;

		for (let i = min; i <= max; i++) {
			array[j] = i;
			j++;
		}

		return array;
	}
</script>