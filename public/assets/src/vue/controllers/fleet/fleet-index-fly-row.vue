<template>
	<div class="row page-fleet-fly-item">
		<div class="col-3 col-sm-1 th">{{ i + 1 }}</div>
		<div class="col-6 col-sm-2 th">
			<a>{{ $root.getLang('FLEET_MISSION', item.mission) }}</a>
			<div v-if="item.start.time + 1 === item.target.time">
				<a title="Возврат домой">(R)</a>
			</div>
			<div v-else>
				<a title="Полёт к цели">(A)</a>
			</div>
		</div>
		<div class="col-3 col-sm-1 th">
			<a class="tooltip">
				<div class="tooltip-content">
					<div v-for="(fleetData, fleetId) in item.units">
						{{ $root.getLang('TECH', fleetId) }}: {{ fleetData['count'] }}
					</div>
				</div>
				{{ item['amount']|number }}
			</a>
		</div>
		<div class="col-4 col-sm-3 th">
			<div>
				<router-link :to="'/galaxy/'+item['target']['galaxy']+'/'+item['target']['system']+'/'" class="negative">
					[{{ item['target']['galaxy'] }}:{{ item['target']['system'] }}:{{ item['target']['planet'] }}]
				</router-link>
			</div>
			{{ item['start']['time']|date('d.m H:i:s') }}
			<timer :value="item['start']['time']" delimiter="" class="positive"></timer>
		</div>
		<div v-if="item['target']['time']" class="col-4 col-sm-3 th">
			<div>
				<router-link :to="'/galaxy/'+item['start']['galaxy']+'/'+item['start']['system']+'/'" class="positive">
					[{{ item['start']['galaxy'] }}:{{ item['start']['system'] }}:{{ item['start']['planet'] }}]
				</router-link>
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

			<router-link v-if="item['stage'] === 0 && item['mission'] === 1 && item.target.id !== 1" :to="'/fleet/verband/id/'+item.id+'/'" class="button">
				Объединить
			</router-link>

			<router-form v-if="item['stage'] === 3 && item['mission'] !== 15" action="/fleet/back/">
				<input name="fleetid" :value="item.id" type="hidden">
				<input value="Отозвать" type="submit" name="send">
			</router-form>
		</div>
	</div>
</template>

<script>
	export default {
		name: "fleet-index-fly-row",
		props: {
			i: Number,
			item: Object
		}
	}
</script>