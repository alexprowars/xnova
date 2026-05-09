<template>
	<Head title="Симулятор"/>
	<div class="table-responsive">
		<form method="get" action="/sim/report" name="form" ref="resultRef" autocomplete="off" target="_blank">
			<input type="hidden" name="r" value="">
		</form>
		<table ref="formRef" class="table">
			<tbody>
				<tr>
					<td class="th">XNova SIM</td>
					<td :colspan="cols - 1" class="th spezial">
						<select size="1" v-model.number="attackers">
							<option v-for="i in slots['max']" :value="i">{{ i }}</option>
						</select>

						Исходная ситуация

						<select size="1" v-model.number="defenders">
							<option v-for="i in slots['max']" :value="i">{{ i }}</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="th">&nbsp;</td>
					<td class="th">Ведущий</td>
					<td v-for="i in Math.min(slots['max'], attackers) - 1" class="th angreifer leftcol_data">
						Атакующий&nbsp;{{ i }}
					</td>
					<td class="th">Планета</td>
					<td v-for="i in Math.min(slots['max'], defenders) - 1" class="th angreifer leftcol_data">
						Защитник&nbsp;{{ i }}
					</td>
				</tr>
				<tr>
					<td class="c" :colspan="cols">Исследования и офицеры</td>
				</tr>
				<tr v-for="techId in tech" align="center">
					<td class="th">{{ $t('tech.'+techId) }}</td>

					<td class="th" v-for="i in range(0, Math.min(slots['max'], attackers) - 1)">
						<input class="number" :value="slots['attackers'][i] !== undefined && slots['attackers'][i][techId] !== undefined ? slots['attackers'][i][techId]['c'] : 0" type="text" :name="'gr'+i+'-'+techId" maxlength="2">
					</td>

					<td class="th" v-for="i in range(0, Math.min(slots['max'], defenders) - 1)">
						<input class="number" :value="slots['defenders'][i] !== undefined && slots['defenders'][i][techId] !== undefined ? slots['defenders'][i]['c'] : 0" type="text" :name="'gr'+(i +slots['max'])+'-'+techId" maxlength="2">
					</td>
				</tr>
				<tr>
					<td class="c" :colspan="cols">Флот</td>
				</tr>
				<tr v-for="fleetId in Object.keys($tm('tech')).filter((v) => v > 200 && v < 300)" align="center">
					<td class="th">{{ $t('tech.' + fleetId) }}</td>

					<td class="th" v-for="i in range(0, Math.min(slots['max'], attackers) - 1)">
						<template v-if="parseInt(fleetId) === 212">-</template>
						<input v-else class="number" :value="slots['attackers'][i] !== undefined && slots['attackers'][i][fleetId] !== undefined ? slots['attackers'][i][fleetId]['c'] : 0" type="text" :name="'gr'+i+'-'+fleetId" maxlength="7">
					</td>

					<td class="th" v-for="i in range(0, Math.min(slots['max'], defenders) - 1)">
						<input class="number" :value="slots['defenders'][i] !== undefined && slots['defenders'][i][fleetId] !== undefined ? slots['defenders'][i][fleetId]['c'] : 0" type="text" :name="'gr'+(i +slots['max'])+'-'+fleetId" maxlength="7">
					</td>
				</tr>
				<tr>
					<td class="c" :colspan="cols">Оборона</td>
				</tr>
				<tr v-for="fleetId in Object.keys($tm('tech')).filter((v) => v > 400 && v < 500)" align="center">
					<td class="th">{{ $t('tech.' + fleetId) }}</td>

					<td class="th" v-for="i in range(0, Math.min(slots['max'], attackers) - 1)">
						-
					</td>

					<td class="th" v-for="i in range(0, Math.min(slots['max'], defenders) - 1)">
						<template v-if="(parseInt(fleetId) === 407 || parseInt(fleetId) === 408) && i > 0">-</template>
						<input v-else class="number" :value="slots['defenders'][i] !== undefined && slots['defenders'][i][fleetId] !== undefined ? slots['defenders'][i][fleetId]['c'] : 0" type="text" :name="'gr'+(i +slots['max'])+'-'+fleetId" maxlength="7">
					</td>
				</tr>
				<tr align="center">
					<td class="th">&nbsp;</td>
					<td class="th" v-for="i in range(0, Math.min(slots['max'], attackers) - 1)"><a href="" @click.prevent="clear(i)">Очистить</a></td>
					<td class="th" v-for="i in range(0, Math.min(slots['max'], defenders) - 1)"><a href="" @click.prevent="clear(slots['max'] + i)">Очистить</a></td>
				</tr>
				<tr>
					<td class="th text-center" :colspan="cols">
						<input class="button" type="button" value="Симуляция" @click.prevent="calculate">
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script setup>
	import { computed, ref, useTemplateRef } from 'vue';
	import { Head, setLayoutProps } from '@inertiajs/vue3';

	defineOptions({
		layout: {
			view: {
				resources: false,
			}
		}
	});

	setLayoutProps({
		appClass: 'set_sim',
	});

	defineProps({
		tech: Array,
		slots: Object,
	});

	const attackers = ref(1);
	const defenders = ref(1);
	const formRef = useTemplateRef('formRef');
	const resultRef = useTemplateRef('resultRef');

	const cols = computed(() => {
		return attackers.value + defenders.value + 1;
	});

	function clear (index) {
		document.querySelectorAll('input[type=text][name^="gr'+index+'-"]').forEach((el) => el.value = '0');
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