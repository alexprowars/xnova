<template>
	<div class="container-fluid">
		<game-page-galaxy-selector v-bind:shortcuts="page.shortcuts"></game-page-galaxy-selector>
		<div class="separator"></div>

		<div class="table-responsive">
			<table class="table galaxy">
				<tr>
					<td class="c" colspan="9">Солнечная система {{ page.galaxy }}:{{ page.system }}</td>
				</tr>
				<tr>
					<td class="c">№</td>
					<td class="c">&nbsp;</td>
					<td class="c">Планета</td>
					<td class="c">Луна</td>
					<td class="c">ПО</td>
					<td class="c">Игрок</td>
					<td class="c">&nbsp;</td>
					<td class="c">Альянс</td>
					<td class="c">Действия</td>
				</tr>

				<tr is="game-page-galaxy-item" v-for="(item, index) in page.items" v-bind:item="item" v-bind:i="index"></tr>

				<tr>
					<th width="30">16</th>
					<th colspan="8" class="c big">
						<a v-bind:href="$root.getUrl('fleet/g'+page.galaxy+'/s'+page.system+'/p16/t0/m15/')">неизведанные дали</a>
					</th>
				</tr>
				<tr>
					<td class="c" colspan="6">
						<span v-if="planets === 1">{{ planets }} заселённая планета</span>
						<span v-else-if="planets === 1">нет заселённых планет</span>
						<span v-else>{{ planets }} заселённые планеты</span>
					</td>
					<td class="c" colspan=3>
						<a class="tooltip_sticky">
							<div class="tooltip-content">
								<table width="240">
									<tr>
										<td width="220">Сильный игрок</td>
										<td><span class="strong">S</span></td>
									</tr>
									<tr>
										<td>Слабый игрок</td>
										<td><span class="noob">N</span></td>
									</tr>
									<tr>
										<td>Режим отпуска</td>
										<td><span class="vacation">U</span></td>
									</tr>
									<tr>
										<td>Заблокирован</td>
										<td><span class="banned">G</span></td>
									</tr>
									<tr>
										<td>Неактивен 7 дней</td>
										<td><span class="inactive">i</span></td>
									</tr>
									<tr>
										<td>Неактивен 28 дней</td>
										<td><span class="longinactive">iI</span></td>
									</tr>
									<tr>
										<td><font color="red">Администратор</font></td>
										<td><font color="red">A</font></td>
									</tr>
									<tr>
										<td><font color="green">Оператор</font></td>
										<td><font color="green">GO</font></td>
									</tr>
									<tr>
										<td><font color="orange">Супер оператор</font></td>
										<td><font color="orange">SGO</font></td>
									</tr>
								</table>
							</div>
							Легенда
						</a>
					</td>
				</tr>
				<tr>
					<td class="c" colspan="3"><span id="missiles">{{ page.user.interplanetary_misil }}</span> межпланетных ракет</td>
					<td class="c" colspan="3"><span id="slots">{{ page.user.fleets }}</span>/{{ page.user.max_fleets }} флотов</td>
					<td class="c" colspan="3">
						<span id="recyclers">{{ Format.number(page.user.recycler) }}</span> переработчиков<br>
						<span id="probes">{{ Format.number(page.user.spy_sonde) }}</span> шпионских зондов
					</td>
				</tr>
			</table>
		</div>
	</div>
</template>

<script>
	export default {
		name: "galaxy",
		props: ['page'],
		components: {
			'game-page-galaxy-item': require('./galaxy-row.vue'),
			'game-page-galaxy-selector': require('./galaxy-selector.vue'),
		},
		computed: {
			planets: function ()
			{
				var count = 0;

				this.page.items.forEach(function(item)
				{
					if (item !== false)
						count++;
				});

				return count;
			}
		},
		methods: {
		}
	}
</script>