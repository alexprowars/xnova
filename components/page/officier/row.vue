<template>
	<div class="row officiers-item">
		<div class="col-12 officiers-item-title">
			{{ $t('TECH.'+item['id']) }}
			(<span v-if="item['time'] > 0" class="positive">Нанят до: {{ item['time'] | date('d.m.Y H:i') }}</span><span v-else class="negative">Не нанят</span>)
		</div>
		<div class="d-none d-sm-block col-sm-2 text-center officiers-item-image">
			<img :src="'/images/officiers/'+item['id']+'.jpg'" align="top" alt="">
		</div>
		<div class="col-12 col-sm-7 text-left officiers-item-description">
			{{ item['description'] }}
			<table class="powers">
				<tbody>
				<tr>
					<td :rowspan="(item['power'].length + 1)" valign="top" class="padding-0">
						<img :src="'/images/officiers/'+item['id']+'.gif'" :alt="$t('TECH.'+item['id'])">
					</td>
				</tr>
				<tr v-for="power in item['power']">
					<td class="up">{{ power }}</td>
				</tr>
				</tbody>
			</table>
		</div>
		<div class="clearfix d-sm-none">
			<div class="separator"></div>
		</div>
		<div class="col-6 d-sm-none text-center officiers-item-image">
			<img :src="'/images/officiers/'+item['id']+'.jpg'" align="top" alt="">
		</div>
		<div class="col-6 col-sm-3 text-center officiers-item-action">
			<div class="negative">{{ item['time'] > 0 ? 'Продлить' : 'Нанять' }}</div>

			<button @click.prevent="submit(7, 20)">на неделю</button>
			<br>Стоимость:&nbsp;<font color="lime">20</font>&nbsp;кр.

			<div class="separator"></div>

			<button @click.prevent="submit(14, 40)">на 2 недели</button>
			<br>Стоимость:&nbsp;<font color="lime">40</font>&nbsp;кр.

			<div class="separator"></div>

			<button @click.prevent="submit(30, 80)">на месяц</button>
			<br>Стоимость:&nbsp;<font color="lime">80</font>&nbsp;кр.

			<div class="separator"></div>
		</div>
	</div>
</template>

<script>
	export default {
		name: "officier-row",
		props: {
			item: Object
		},
		methods: {
			submit (value, price)
			{
				this.$dialog
					.confirm({
						body: 'Вы действительно хотите нанять офицера "<b>'+this.$t('TECH.'+this.item['id'])+'</b>" на <b>'+value+'</b> дней за <b>'+price+'</b> кредитов?',
						title: 'Вербовка офицера'
					}, {
						okText: 'Нанять',
						cancelText: 'Отменить',
					})
					.then(() =>
					{
						this.$post('/officier/buy/', {
							id: this.item['id'],
							duration: value
						})
						.then((result) => {
							this.$store.commit('PAGE_LOAD', result);
						})
					})
			}
		}
	}
</script>