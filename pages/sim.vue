<template>
	<div class="table-responsive">
		<form method="post" :action="'/xnsim/report/'" name="form" ref="result" autocomplete="off" target="_blank">
			<input type="hidden" name="r" value="">
		</form>
		<table ref="form" class="table">
			<tbody>
				<tr>
					<th>XNova SIM</th>
					<th :colspan="cols - 1" class="spezial">

						<select size="1" v-model.number="attackers" title="">
							<option v-for="i in page['slots']['max']" :value="i">{{ i }}</option>
						</select>

						Исходная ситуация

						<select size="1" v-model.number="defenders" title="">
							<option v-for="i in page['slots']['max']" :value="i">{{ i }}</option>
						</select>

					</th>
				</tr>
				<tr>
					<th>&nbsp;</th>
					<th>Ведущий</th>
					<th v-for="i in page['slots']['max'] - 1" v-if="i < attackers" class="angreifer leftcol_data">
						Атакующий&nbsp;{{ i }}
					</th>
					<th>Планета</th>
					<th v-for="i in page['slots']['max'] - 1" v-if="i < defenders" class="angreifer leftcol_data">
						Защитник&nbsp;{{ i }}
					</th>
				</tr>
				<tr>
					<td class="c" :colspan="cols">Исследования и офицеры</td>
				</tr>
				<tr v-for="techId in page['tech']" align="center">
					<th>{{ $t('TECH.'+techId) }}</th>

					<th v-for="i in range(0, page['slots']['max'] - 1)" v-if="i < attackers">
						<input class="number" :value="page['slots']['attackers'][i] !== undefined && page['slots']['attackers'][i][techId] !== undefined ? page['slots']['attackers'][i][techId]['c'] : 0" type="text" :name="'gr'+i+'-'+techId" maxlength="2" title="">
					</th>

					<th v-for="i in range(0, page['slots']['max'] - 1)" v-if="i < defenders">
						<input class="number" :value="page['slots']['defenders'][i] !== undefined && page['slots']['defenders'][i][techId] !== undefined ? page['slots']['defenders'][i]['c'] : 0" type="text" :name="'gr'+(i +page['slots']['max'])+'-'+techId" maxlength="2" title="">
					</th>
				</tr>
				<tr>
					<td class="c" :colspan="cols">Флот</td>
				</tr>
				<tr v-for="(name, fleetId) in $t('TECH')" v-if="fleetId > 200 && fleetId < 300" align="center">
					<th>{{ name }}</th>

					<th v-for="i in range(0, page['slots']['max'] - 1)" v-if="i < attackers">
						<template v-if="parseInt(fleetId) === 212">-</template>
						<input v-else class="number" :value="page['slots']['attackers'][i] !== undefined && page['slots']['attackers'][i][fleetId] !== undefined ? page['slots']['attackers'][i][fleetId]['c'] : 0" type="text" :name="'gr'+i+'-'+fleetId" maxlength="7" title="">
					</th>

					<th v-for="i in range(0, page['slots']['max'] - 1)" v-if="i < defenders">
						<input class="number" :value="page['slots']['defenders'][i] !== undefined && page['slots']['defenders'][i][fleetId] !== undefined ? page['slots']['defenders'][i][fleetId]['c'] : 0" type="text" :name="'gr'+(i +page['slots']['max'])+'-'+fleetId" maxlength="7" title="">
					</th>
				</tr>
				<tr>
					<td class="c" :colspan="cols">Оборона</td>
				</tr>
				<tr v-for="(name, fleetId) in $t('TECH')" v-if="fleetId > 400 && fleetId < 500" align="center">
					<th>{{ name }}</th>

					<th v-for="i in range(0, page['slots']['max'] - 1)" v-if="i < attackers">
						-
					</th>

					<th v-for="i in range(0, page['slots']['max'] - 1)" v-if="i < defenders">
						<template v-if="(parseInt(fleetId) === 407 || parseInt(fleetId) === 408) && i > 0">-</template>
						<input v-else class="number" :value="page['slots']['defenders'][i] !== undefined && page['slots']['defenders'][i][fleetId] !== undefined ? page['slots']['defenders'][i][fleetId]['c'] : 0" type="text" :name="'gr'+(i +page['slots']['max'])+'-'+fleetId" maxlength="7" title="">
					</th>
				</tr>
				<tr align="center">
					<th>&nbsp;</th>
					<th v-for="i in range(0, (page['slots']['max'] - 1))" v-if="i < attackers"><a href="" @click.prevent="clear(i)">Очистить</a></th>
					<th v-for="i in range(0, (page['slots']['max'] - 1))" v-if="i < defenders"><a href="" @click.prevent="clear(page['slots']['max'] + i)">Очистить</a></th>
				</tr>
				<tr>
					<th :colspan="cols">
						<input class="button" type="button" value="Симуляция" @click.prevent="calculate">
					</th>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
	export default {
		name: 'sim',
		async asyncData ({ store }) {
			return await store.dispatch('loadPage')
		},
		watchQuery: true,
		data () {
			return {
				attackers: 1,
				defenders: 1,
			}
		},
		computed: {
			cols () {
				return this.attackers + this.defenders + 1;
			}
		},
		methods: {
			clear (index) {
				$('input[type=text][name^="gr'+index+'-"]').val('0');
			},
			calculate ()
			{
				let txt = "", tstr = "", tkey, tval;
				tkey = [];

				$(this.$refs['form']).find('input[type="text"][name^="gr"]').each(function()
				{
					if (this.value > 0)
					{
						tstr = this.name;
						tval = tstr.split("-");

						tval[0] = parseInt(tval[0].split('gr').join(''));

						if (tkey[tval[0]])
							tkey[tval[0]] += parseInt(tval[1]) + ',' + this.value + ';';
						else
							tkey[tval[0]] = parseInt(tval[1]) + ',' + this.value + ';';
					}
				});

				if (tkey.length > 0)
				{
					for (let i = 0; i < tkey.length; i++)
					{
						if (tkey[i])
							txt += tkey[i] + '|';
						else
							txt += '|';
					}
				}

				$(this.$refs['result']).find('input').val(txt);
				$(this.$refs['result']).submit();
			},
			range: function (min, max)
			{
				let array = [], j = 0;

				for (let i = min; i <= max; i++)
				{
					array[j] = i;
					j++;
				}

				return array;
			},
		}
	}
</script>