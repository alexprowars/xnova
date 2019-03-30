<template>
	<div class="row page-fleet-fly-item">
		<div class="col-3 col-sm-1 th">{{ i + 1 }}</div>
		<div class="col-6 col-sm-2 th">
			<a>{{ $t('FLEET_MISSION.'+item.mission) }}</a>
			<div v-if="item.start.time + 1 === item.target.time">
				<a title="Возврат домой">(R)</a>
			</div>
			<div v-else>
				<a title="Полёт к цели">(A)</a>
			</div>
		</div>
		<div class="col-3 col-sm-1 th">
			<Popper>
				<div v-for="(fleetData, fleetId) in item.units">
					{{ $t('TECH.'+fleetId) }}: {{ fleetData['count'] }}
				</div>
				<template slot="reference">
					{{ item['amount'] | number }}
				</template>
			</Popper>
		</div>
		<div class="col-4 col-sm-3 th">
			<div>
				<nuxt-link :to="'/galaxy/?galaxy='+item['target']['galaxy']+'&system='+item['target']['system']" class="negative">
					[{{ item['target']['galaxy'] }}:{{ item['target']['system'] }}:{{ item['target']['planet'] }}]
				</nuxt-link>
			</div>
			{{ item['start']['time']|date('d.m H:i:s') }}
			<timer :value="item['start']['time']" delimiter="" class="positive"></timer>
		</div>
		<div v-if="item['target']['time']" class="col-4 col-sm-3 th">
			<div>
				<nuxt-link :to="'/galaxy/?galaxy='+item['start']['galaxy']+'&system='+item['start']['system']" class="positive">
					[{{ item['start']['galaxy'] }}:{{ item['start']['system'] }}:{{ item['start']['planet'] }}]
				</nuxt-link>
			</div>
			{{ item['target']['time']|date('d.m H:i:s') }}
			<timer :value="item['target']['time']" delimiter="" class="positive"></timer>
		</div>
		<div v-else class="col-4 col-sm-3 th">
			-
		</div>
		<div class="col-4 col-sm-2 th">
			<router-form v-if="item['stage'] === 0 && item['mission'] !== 20 && item.target.id !== 1" action="/fleet/back/">
				<input name="fleetid" :value="item.id" type="hidden">
				<input value="Возврат" type="submit" name="send">
			</router-form>

			<nuxt-link v-if="item['stage'] === 0 && item['mission'] === 1 && item.target.id !== 1" :to="'/fleet/verband/'+item.id+'/'" class="button">
				Объединить
			</nuxt-link>

			<button v-if="item['stage'] === 3 && item['mission'] !== 15" @click.prevent="backAction">Отозвать</button>
		</div>
	</div>
</template>

<script>
	export default {
		name: "fleet-index-fly-row",
		props: {
			i: Number,
			item: Object
		},
		methods: {
			backAction ()
			{
				this.$dialog
					.confirm({
						body: 'Вернуть флот?',
					}, {
						okText: 'Да',
						cancelText: 'Нет',
					})
					.then(() =>
					{
						this.$post('/fleet/back/', {
							id: this.item['id'],
						})
						.then((result) => {
							this.$store.commit('PAGE_LOAD', result);
						})
					})
			}
		}
	}
</script>