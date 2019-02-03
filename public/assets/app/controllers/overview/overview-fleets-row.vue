<template>
	<div class="row overview-fleets-row">
		<div class="col-3 col-sm-2 th">
			<div class="z">
				<timer :value="item['time']"></timer>
			</div>
			<div class="positive">{{ item['date'] }}</div>
		</div>
		<div class="col-9 col-sm-10 th text-left" :class="[item['status'], item['prefix'], item['mission']]" v-html="item['text']"></div>
	</div>
</template>

<script>
	export default {
		name: "overview-fleets-row",
		props: {
			item: Object
		}
	}
</script>